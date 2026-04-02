## 1. Environment and build verification

- [ ] 1.1 Copy `.env.example` to `.env` at the Laravel root; set `APP_URL`, database, mail, and any API-related variables; run `php artisan key:generate` if needed.
- [ ] 1.2 Run `php artisan migrate` (Asia/Taipei expectations per project rules) and `php artisan serve` (or use Laragon) with the API reachable at the URL used by the SPAs.
- [ ] 1.3 In `system/frontend`, run `pnpm install`, `pnpm dev`, and `pnpm build` with `VITE_API_BASE_URL` pointing at the running API; confirm no build errors.
- [ ] 1.4 In `system/backend`, run `pnpm install`, `pnpm dev`, and `pnpm build` with the same `VITE_API_BASE_URL`; confirm no build errors.

## 2. Integration and API behavior checks

- [ ] 2.1 From the public app, exercise at least one public GET and one public POST (e.g. contact or booking) and confirm JSON responses match expectations.
- [ ] 2.2 From the admin app, log in, call one `auth:sanctum` route, and confirm the Bearer token is sent and 401 handling redirects to login.
- [ ] 2.3 Compare `system/frontend/lib/api.ts` and `system/backend/lib/api.ts` for `getApiBaseUrl` and header differences; note any drift in a short summary for the team.
- [ ] 2.4 Verify `system/backend/components/CKEditor.tsx` API base URL behavior matches `lib/api.ts` when `VITE_API_BASE_URL` is unset.

## 3. Security and configuration hygiene

- [ ] 3.1 Review `routes/api.php` for routes that should not be public in production (e.g. test or debug endpoints); document or gate them.
- [ ] 3.2 Confirm CORS / Sanctum settings in `bootstrap/app.php` and `config/sanctum.php` match the real admin and public origins (including `www` vs apex).
- [ ] 3.3 Document whether onboarding uses root `npm`/`composer setup` vs `pnpm` in `system/*`, and update team docs if the two conflict.

## 4. Spec compliance sign-off

- [ ] 4.1 Walk through `specs/fe-be-integration/spec.md` scenarios against the running stack; mark any gap as a follow-up change.
- [ ] 4.2 If all checks pass, archive or close this change per project OpenSpec workflow; if not, open targeted changes for each blocker.
