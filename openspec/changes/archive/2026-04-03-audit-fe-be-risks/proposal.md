## Why

The repository combines a Laravel 12 API with two separate Vite + React apps (`system/frontend` public site and `system/backend` admin). Integration risks (API base URL resolution, cross-origin auth, duplicated client code, and tooling drift) are easy to miss until deploy or staging. A structured audit clarifies what could break and what to standardize before shipping.

## What Changes

- Produce a documented audit of frontend–backend integration points and operational gaps (not a code refactor in this change).
- Define requirement-level expectations for API configuration, auth usage, and error handling so future work has a clear contract.
- Add an actionable task list to verify builds, environment variables, CORS/Sanctum alignment, and removal or protection of debug-only API routes (e.g. contact test endpoints).
- **BREAKING**: None by itself; follow-on fixes may affect deploy config or Sanctum stateful domains.

## Capabilities

### New Capabilities

- `fe-be-integration`: Covers consistent API base URL strategy (`VITE_API_BASE_URL` vs hostname fallbacks), Bearer token usage for admin, public vs protected routes, and predictable JSON error responses across both React clients.

### Modified Capabilities

- _(none — `openspec/specs/` has no existing capability specs to delta.)_

## Impact

- **Code areas**: `system/frontend/lib/api.ts`, `system/backend/lib/api.ts`, `system/backend/components/CKEditor.tsx` (duplicate API base default), Laravel `routes/api.php`, `bootstrap/app.php` (CORS comment), `config/sanctum.php`.
- **Tooling**: Root `package.json` uses Vite 7 + pnpm; `system/frontend` and `system/backend` use Vite 6 — different lockfiles/install paths can confuse `composer` setup scripts that call `npm install` at repo root vs app folders.
- **Dependencies**: Laravel Sanctum, `fetch`-based clients (no shared package between the two React apps today).
