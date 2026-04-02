## Context

The Laravel API (`routes/api.php`) exposes many resources; Sanctum protects admin-style routes. Two React SPAs live under `system/frontend` and `system/backend`, each with its own `lib/api.ts` that resolves `VITE_API_BASE_URL` or falls back to hard-coded production hostnames and `http://localhost:8000/api`. CORS is noted in `bootstrap/app.php`. Composer’s `setup` script runs `npm install` and `npm run build` at the repository root, while React apps may be developed with pnpm in subfolders (per project conventions). These layers can drift: wrong API host in staging, token not sent on public client, or admin-only routes accidentally assumed on the public app.

## Goals / Non-Goals

**Goals:**

- Establish a repeatable audit checklist that validates configuration, auth, and client behavior against the Laravel API.
- Record technical decisions for how API base URLs and credentials should be managed across environments.
- Identify concrete risks (duplication, test routes, version skew) and mitigations without blocking on a full shared SDK.

**Non-Goals:**

- Rewriting both React apps into a monorepo package or merging Vite versions in this change.
- Full security penetration test or performance benchmarking.
- Changing production domains or infrastructure (only documenting what must align).

## Decisions

1. **Single source of truth for API URL**  
   - **Decision**: Prefer `VITE_API_BASE_URL` in all deployed environments; treat hostname fallbacks in `getApiBaseUrl()` as a safety net for known production domains, not as the primary config.  
   - **Rationale**: Build-time env is explicit in CI/CD; hard-coded hostnames rot when adding staging or new domains.  
   - **Alternatives**: Remove fallbacks entirely (stricter, fails fast if env missing); keep only fallbacks (current ease, higher drift risk).

2. **Auth model**  
   - **Decision**: Admin SPA continues to use Bearer tokens in `localStorage` as today; public frontend must not assume Sanctum on every call—only endpoints documented as public should be used without credentials.  
   - **Rationale**: Matches existing `ApiClient` vs `FrontendApiClient` split.  
   - **Alternatives**: Cookie-based Sanctum SPA auth for admin (more moving parts with cross-origin).

3. **Audit execution**  
   - **Decision**: Run `pnpm install`, `pnpm dev`, and `pnpm build` inside each React app folder after Laravel `.env` is set; verify representative API calls against `php artisan serve` or the real host.  
   - **Rationale**: Matches stated project conventions and catches missing env at build time where Vite inlines variables.

## Risks / Trade-offs

- **[Risk] Duplicate `getApiBaseUrl` logic** in two files → **Mitigation**: Future refactor to a shared package or single copied module with tests; during audit, diff the two files and document any intentional divergence (e.g. admin-only headers).
- **[Risk] CKEditor default** `VITE_API_BASE_URL || 'http://localhost:8000/api'` diverges from `lib/api.ts` if env is unset → **Mitigation**: Ensure `.env` is mandatory in docs; align defaults in a follow-up change if audit finds mismatches.
- **[Risk] Public test routes** (e.g. contact test) exposed in production → **Mitigation**: Gate with environment or remove; verify in audit.
- **[Risk] Root `npm` vs app `pnpm`** → **Mitigation**: Document which commands apply to which tree; align CI to install/build both SPAs explicitly.

## Migration Plan

1. Run the checklist in `tasks.md` on a clean clone (or CI job).  
2. Fix any **blocker** findings (wrong API URL, open test endpoints, CORS blocking admin origin) before release.  
3. Track **non-blocker** items (Vite version alignment, shared client) as separate changes.  
4. **Rollback**: This change is documentation/spec only; no deploy rollback required. If follow-up code changes ship, revert those commits per normal process.

## Open Questions

- Are staging URLs required in `getApiBaseUrl()` or only via `VITE_API_BASE_URL`?  
- Should admin and public apps ever share the same origin with Laravel (same-site) in production, or stay on separate subdomains?  
- Is `composer setup` the canonical onboarding path, or should docs point to pnpm in `system/*` only?
