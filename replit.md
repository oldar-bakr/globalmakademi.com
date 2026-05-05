# Workspace

## Overview

pnpm workspace monorepo using TypeScript. Each package manages its own dependencies.

## Stack

- **Monorepo tool**: pnpm workspaces
- **Node.js version**: 24
- **Package manager**: pnpm
- **TypeScript version**: 5.9
- **API framework**: Express 5
- **Database**: PostgreSQL + Drizzle ORM
- **Validation**: Zod (`zod/v4`), `drizzle-zod`
- **API codegen**: Orval (from OpenAPI spec)
- **Build**: esbuild (CJS bundle)

## Key Commands

- `pnpm run typecheck` — full typecheck across all packages
- `pnpm run build` — typecheck + build all packages
- `pnpm --filter @workspace/api-spec run codegen` — regenerate API hooks and Zod schemas from OpenAPI spec
- `pnpm --filter @workspace/db run push` — push DB schema changes (dev only)
- `pnpm --filter @workspace/api-server run dev` — run API server locally

See the `pnpm-workspace` skill for workspace structure, TypeScript setup, and package details.

## Deliverable: Makademi Training Hub (PHP + MySQL on Hostinger)

A PHP-rendered website for Makademi Training & Consultancy. The site lives in `makademi-website/` and ships as `makademi-website.zip` (~31 MB) for upload to Hostinger Business plan.

### Architecture

- **Public pages**: `index.html`, `about.html`, `contact.html`, `404.html`, two `courses/*.html` detail pages — plain HTML.
- **DB-driven pages**: `courses.php` (101 programs catalog with search/filter) and `gallery.php` (categorised photo grid with lightbox) read from the database.
- **Admin portal** (`admin/`): password-protected CRUD for programs and gallery photos. The user can add/remove courses and upload images without ever editing code.
- **Database**: MySQL (production on Hostinger); SQLite (local Replit dev). One PDO codepath, two DSNs.
- **Design system unchanged**: Navy (#00234B) + Gold (#D4AF37), Inter + DM Sans.

### Layout

```
makademi-website/
  index.html  about.html  contact.html  404.html  config.example.php
  courses.php  gallery.php
  admin/
    setup-account.php  login.php  logout.php  index.php
    programs.php  gallery.php  _header.php  _footer.php
  includes/
    config.php (gitignored, dev = SQLite; on Hostinger user creates from config.example.php)
    db.php  auth.php  csrf.php  helpers.php
    partials/{header,footer,lightbox}.php
  db/
    setup.sql           (MySQL schema + 101 programs + 6 gallery photos seed)
    setup.sqlite.sql    (mirror for local dev)
    makademi.sqlite     (gitignored, local only)
  assets/css/styles.css + admin.css ; assets/js/main.js
  assets/images/gallery/   (auto-populated by admin uploads)
  ADMIN_SETUP.md  README.txt  CONTACT_FORM_SETUP.md
scripts/extract-makademi-content.mjs  (one-shot: parses old courses.html + gallery.html → seed SQL)
```

### Security model

- Single admin user (bcrypt). Per-session brute-force throttle (5 fails → 60s lock).
- All POST routes CSRF-checked; logout is POST-only.
- Bootstrap-takeover protection: `/admin/setup-account.php` requires the operator to paste the `app_secret` from `includes/config.php` and refuses to run while `app_secret` is the placeholder. Triple lock: marker file + DB row count + secret check.
- Setup race: SQLite `BEGIN IMMEDIATE` / MySQL transaction wrap COUNT+INSERT atomically.
- Login `next=` param is allowlisted to known admin pages (no open-redirect).
- File uploads validate MIME via `finfo` (`jpg/png/webp/gif`), 10 MB cap, random-hex filename, basename-only unlink (no path traversal).

### Workspace Preview

Served via the `makademi-portal` artifact (`artifacts/makademi-portal/`) using PHP's built-in dev server: `php -S 0.0.0.0:22731 -t /home/runner/workspace/makademi-website`. The dev `includes/config.php` points at SQLite at `db/makademi.sqlite`, with a non-placeholder `app_secret` (`replit-local-dev-secret-d4e7c0a3-not-for-public-deploy`) so the local setup flow works end-to-end.

To exercise the admin locally: visit `/admin/setup-account.php`, paste the dev `app_secret` from `includes/config.php`, pick a username + password (≥12 chars), then sign in at `/admin/login.php`.

### Hostinger deployment

End-user follows `ADMIN_SETUP.md` (in the zip): upload zip → create MySQL DB in hPanel → copy `config.example.php` to `includes/config.php` and fill in DB creds + a strong `app_secret` → import `db/setup.sql` via phpMyAdmin → visit `/admin/setup-account.php` once with the secret → log in.

### Rebuilding the zip

`python3 /tmp/build_zip.py` (script kept under `/tmp`, not committed) walks `makademi-website/` and excludes: `db/makademi.sqlite`, `includes/config.php`, `admin/.installed`, `data/extracted.json`. The destructive zip-modify tooling (`adm-zip`) was removed earlier — Python's stdlib `zipfile` is the canonical builder now.

### Hostinger Git deploy (two-branch setup)

For users who prefer `git pull` over zip upload, the live site is served from a generated `hostinger-deploy` branch on `origin` whose **root** contains only the contents of `makademi-website/` (no `package.json`, no `artifacts/`, no `pnpm-workspace.yaml`). Hostinger's Git integration is pointed at that branch and pulls it directly into `public_html/`.

Re-syncing after a code change to `makademi-website/`:

```bash
./scripts/sync-hostinger-deploy.sh
```

The script runs three steps: (1) `git subtree split --prefix=makademi-website -b hostinger-deploy`, (2) in a throwaway worktree it `git rm`'s the dev-only files (`includes/config.php`, `admin/.installed`, `db/makademi.sqlite`, `db/makademi.sqlite-journal`, `data/extracted.json`) and commits the removal, (3) `git push --force-with-lease origin hostinger-deploy`. It refuses to run with uncommitted changes inside `makademi-website/`. End-user instructions are in `HOSTINGER_GIT.md` at the repo root. The zip-upload path (`makademi-website.zip` + File Manager) remains supported as a fallback.

> **Critical**: `makademi-website/includes/config.php` is currently tracked on `main` (it's the dev SQLite config that lets the site run out-of-the-box in Replit). The sync script's scrub step is what keeps that file out of the deploy branch — without it, every `git pull` on Hostinger would overwrite the production credentials with the dev ones. If you ever modify the script, the `EXCLUDE` list at the top is load-bearing.

> **First push gotcha**: the main agent's git wrapper blocks `git subtree split` and `git push`. The first execution of the sync script needs to be run from a developer's local clone (or by reassigning a Hostinger-deploy task to a Replit task agent in an isolated environment). Subsequent runs are no different.

Verification of the deploy branch contents (without actually pushing) — useful in CI or after editing the EXCLUDE list. This uses `git ls-tree` (not the working tree) so it accurately reflects what `git subtree split` would produce, since untracked files are never published:

```bash
SUBTREE=$(git ls-tree HEAD makademi-website | awk '{print $3}')
echo "--- top-level of deploy branch ---"
git ls-tree --name-only "$SUBTREE" | sort
echo "--- includes/ (config.php must be removed by the script before push) ---"
git ls-tree --name-only "$SUBTREE" includes/ | sort
```

When run today against `main`, the **top level** of the deploy branch is exactly: `404.html about.html admin ADMIN_SETUP.md apple-touch-icon.png assets config.example.php CONTACT_FORM_SETUP.md contact.html courses courses.php db favicon-16.png favicon-32.png favicon.ico gallery.php includes index.html README.txt` (19 entries). Note: `package.json`, `artifacts/`, `pnpm-workspace.yaml`, `replit.md`, `scripts/`, and `.local/` are absent (different folder), and `data/` is absent (its only file `extracted.json` is gitignored, so the directory has no tracked content and never appears on the deploy branch). Inside `includes/`, the tracked files include `config.php` — the sync script's scrub step removes it before push, so the published deploy branch's `includes/` contains only `auth.php csrf.php db.php helpers.php partials/`.
