## ADDED Requirements

### Requirement: API base URL configuration
Both the public frontend and the admin frontend SHALL resolve the Laravel API base URL in a predictable way for every environment.

#### Scenario: Environment variable is set
- **WHEN** `VITE_API_BASE_URL` is defined at build time for either SPA
- **THEN** all API requests from that SPA SHALL use that value as the API prefix (including scheme and `/api` path as configured)

#### Scenario: Fallback hostnames
- **WHEN** `VITE_API_BASE_URL` is not set and the browser hostname matches a documented production host
- **THEN** the client SHALL use the corresponding documented HTTPS API base URL for that host

#### Scenario: Local development default
- **WHEN** `VITE_API_BASE_URL` is not set and the hostname is not a documented production host
- **THEN** the client SHALL default to `http://localhost:8000/api` unless overridden by project-local documentation

### Requirement: Authenticated admin API access
The admin SPA SHALL send credentials required by Laravel Sanctum for protected routes.

#### Scenario: Bearer token attached
- **WHEN** the admin SPA calls an endpoint guarded by `auth:sanctum` and a token exists in storage
- **THEN** the request SHALL include an `Authorization: Bearer <token>` header

#### Scenario: Unauthorized response
- **WHEN** the API returns HTTP 401 for an admin request
- **THEN** the admin client SHALL clear the stored token and redirect the user to the login route as implemented today

### Requirement: Public frontend API usage
The public frontend SHALL only rely on API routes that are intended to be public; it MUST NOT assume admin-only endpoints are callable without authentication.

#### Scenario: Public POST without token
- **WHEN** the public site submits a form or booking to a documented public POST route
- **THEN** the request SHALL succeed without a Bearer token if the Laravel route is defined without `auth:sanctum`

### Requirement: JSON error shape for clients
API error responses used by the React clients SHALL be parseable as JSON when the API returns an error status, so clients can surface `message` (and validation `errors` where applicable).

#### Scenario: Client handles non-JSON error body
- **WHEN** the server returns a non-JSON body or network failure occurs
- **THEN** the client SHALL fail safely without assuming `message` exists (e.g. catch parse errors and show a generic failure)

### Requirement: Cross-origin and Sanctum alignment
Deployments where the SPA origin differs from the API origin SHALL permit browser calls: CORS MUST allow the SPA origins that are in use, and Sanctum stateful domain configuration MUST include those origins if cookie/session auth is used in the future.

#### Scenario: Separate subdomain deployment
- **WHEN** the admin or public app is served from a hostname different from the API hostname
- **THEN** the deployment checklist SHALL verify CORS and Sanctum settings explicitly for that pair before go-live
