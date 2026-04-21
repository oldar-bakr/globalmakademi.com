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

## Static Deliverable: Makademi Training Hub

A plain HTML/CSS/JS static website rebuild of the React Makademi Training Hub portal.

- **Directory**: `makademi-website/`
- **Downloadable**: `makademi-website.zip` (4.8 MB)
- **Pages**: 7 HTML files (index, courses, about, contact, 404, and 2 course detail pages)
- **Courses**: 101 programs with client-side search and category filtering
- **Design**: Navy (#00234B) + Gold (#D4AF37) palette, Inter + DM Sans typography
- **No build step**: Upload directly to Hostinger File Manager
- **Contact form**: FormSubmit.co integration (see README.txt for alternatives)

### Workspace Preview

The static site is also served live in the workspace Preview pane via the `makademi-portal` artifact (`artifacts/makademi-portal/`), a Vite dev server with a custom middleware plugin that serves files from `makademi-website/` at the root path. Extensionless URLs like `/courses` resolve to `courses.html`, and unknown paths fall back to `404.html`. The `api-server` artifact still owns `/api`.

The Preview pane also injects the [Agentation](https://www.npmjs.com/package/agentation) visual feedback toolbar into every served HTML response on the fly (via `src/agentation-init.tsx`). The on-disk files in `makademi-website/` are never modified, so the Hostinger zip stays clean. To use the toolbar to send annotations to a coding agent, the user must run the Agentation MCP server locally (`npx add-mcp` adds `agentation-mcp`).
