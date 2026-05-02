#!/usr/bin/env bash
# Sync the contents of makademi-website/ to the `hostinger-deploy` branch
# on origin so Hostinger's Git integration deploys it cleanly into
# public_html/. Run this from the repo root every time you want to push
# code changes to the live site.
#
# Usage:
#   ./scripts/sync-hostinger-deploy.sh
#
# What it does (idempotent, safe to re-run):
#   1. Re-runs `git subtree split` to materialize the latest state of
#      makademi-website/ as a flat branch.
#   2. Strips dev-only files from that branch in a temporary worktree
#      so production NEVER receives:
#        - includes/config.php  (dev SQLite + dev app_secret)
#        - admin/.installed      (local setup marker)
#        - db/makademi.sqlite    (local DB; usually gitignored anyway)
#        - data/extracted.json   (build artifact; usually gitignored)
#   3. Pushes the scrubbed branch to origin with --force-with-lease so
#      re-syncs after history changes still succeed without clobbering
#      anyone else's pushes.
#   4. Reminds you to trigger the pull in hPanel.
#
# Safety:
#   - Never modifies `main` or any other working branch.
#   - Refuses to run if the working tree has uncommitted changes inside
#     makademi-website/ (you'd be deploying something that isn't on main).
#   - The temp worktree is always cleaned up, even on error.

set -euo pipefail

PREFIX="makademi-website"
BRANCH="hostinger-deploy"
REMOTE="origin"
SOURCE_BRANCH="main"
EXCLUDE=(
  "includes/config.php"
  "admin/.installed"
  "db/makademi.sqlite"
  "db/makademi.sqlite-journal"
  "data/extracted.json"
)

cd "$(git rev-parse --show-toplevel)"

# --- sanity ------------------------------------------------------------

if [[ ! -d "$PREFIX" ]]; then
  echo "ERROR: '$PREFIX/' not found at repo root. Are you in the right repo?" >&2
  exit 1
fi

current_branch="$(git rev-parse --abbrev-ref HEAD)"
if [[ "$current_branch" != "$SOURCE_BRANCH" ]]; then
  echo "ERROR: You are on branch '$current_branch'. Switch to '$SOURCE_BRANCH' first." >&2
  echo "       (The deploy branch is always generated from $SOURCE_BRANCH so" >&2
  echo "       what's pushed matches what's documented.)" >&2
  echo "       To override (rare), set ALLOW_NON_MAIN=1 in the environment." >&2
  if [[ "${ALLOW_NON_MAIN:-0}" != "1" ]]; then
    exit 1
  fi
  echo "       ALLOW_NON_MAIN=1 set; continuing with $current_branch." >&2
fi

if ! git diff --quiet -- "$PREFIX" || ! git diff --cached --quiet -- "$PREFIX"; then
  echo "ERROR: You have uncommitted changes in $PREFIX/." >&2
  echo "       Commit or stash them first so the deploy branch matches $SOURCE_BRANCH." >&2
  exit 1
fi

# --- step 1: subtree split --------------------------------------------

echo "==> Splitting $PREFIX/ into raw $BRANCH ref..."
git branch -D "$BRANCH" 2>/dev/null || true
git subtree split --prefix="$PREFIX" -b "$BRANCH"

# --- step 2: scrub sensitive files in a throwaway worktree -------------

WORKTREE="$(mktemp -d -t hostinger-deploy.XXXXXX)"
cleanup() {
  if git worktree list --porcelain 2>/dev/null | grep -q "$WORKTREE"; then
    git worktree remove --force "$WORKTREE" >/dev/null 2>&1 || true
  fi
  rm -rf "$WORKTREE"
}
trap cleanup EXIT

echo ""
echo "==> Scrubbing dev-only files in temp worktree..."
git worktree add --quiet "$WORKTREE" "$BRANCH"

scrubbed=0
pushd "$WORKTREE" >/dev/null
for f in "${EXCLUDE[@]}"; do
  if git ls-files --error-unmatch "$f" >/dev/null 2>&1; then
    git rm -q -- "$f"
    echo "    stripped $f"
    scrubbed=1
  fi
done

if [[ $scrubbed -eq 1 ]]; then
  GIT_AUTHOR_NAME="hostinger-deploy"   GIT_AUTHOR_EMAIL="deploy@local" \
  GIT_COMMITTER_NAME="hostinger-deploy" GIT_COMMITTER_EMAIL="deploy@local" \
    git commit -q -m "Strip dev-only files for Hostinger deploy"
fi
popd >/dev/null

# --- step 3: push ------------------------------------------------------

echo ""
echo "==> Pushing $BRANCH to $REMOTE..."
git push --force-with-lease "$REMOTE" "$BRANCH"

# --- done --------------------------------------------------------------

echo ""
echo "Done."
echo ""
echo "Next steps on the Hostinger side:"
echo "  1. hPanel -> Websites -> your domain -> Git."
echo "  2. Confirm the deployment branch is set to '$BRANCH' (one-time)."
echo "  3. Click 'Pull' (or wait for auto-pull if you have a webhook)."
echo ""
echo "Your real includes/config.php on the server is NEVER touched by a pull."
