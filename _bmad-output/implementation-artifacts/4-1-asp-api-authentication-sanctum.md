# Story 4.1: ASP API Authentication (Sanctum)

Status: done

<!-- Note: Validation is optional. Run validate-create-story for quality check before dev-story. -->

## Story

As a System Admin,
I want to secure the ASP sync endpoints,
So that only the authorized ASP system can access student data.

## Acceptance Criteria

1. **Given** the ASP system needs to connect
2. **When** it presents a valid Sanctum token with `asp:sync` scope
3. **Then** the request is authorized
4. **And** invalid tokens are rejected with 401 Unauthorized

## Tasks / Subtasks

- [x] Install and Configure Laravel Sanctum
    - [x] Run `php artisan install:api`
    - [x] Verify `config/sanctum.php` exists and is configured
- [x] Update User Model
    - [x] Add `HasApiTokens` trait to `User` model
- [x] Create ASP System User Seeder/Command
    - [x] Create a command `php artisan asp:create-token` to generate a token for the ASP system
    - [x] Ensure token has `asp:sync` ability
- [x] Configure API Routes
    - [x] Define `routes/api.php` structure
    - [x] Create a group protected by `auth:sanctum` and `ability:asp:sync`
- [x] Verification Tests
    - [x] Create `tests/Feature/AspApiAuthTest.php`
    - [x] Test: Request without token -> 401
    - [x] Test: Request with invalid token -> 401
    - [x] Test: Request with valid token but wrong scope -> 403 (if checking ability)
    - [x] Test: Request with valid token and scope -> 200

## Dev Notes

### Architecture Compliance
- **Security:** Strict adherence to "ASP API Security: Laravel Sanctum (API Tokens)" decision.
- **Middleware:** Use `auth:sanctum` for API routes. Ensure `CheckForAnyAbility` or similar middleware is aliased in `bootstrap/app.php` if you use ability checks in routes.
- **Boundaries:** This story focuses on the *Security Layer*. The actual endpoints (`AspSyncController`) will be implemented in Story 4.2, but you should create a placeholder or "ping" endpoint to verify authentication works.

### Implementation Specifics (Laravel 11)
- **Installation:** Use `php artisan install:api`. This handles migration publication and running.
- **Middleware:** In Laravel 11, middleware aliases are defined in `bootstrap/app.php`. You may need to alias `ability` to `Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class`.
- **Token Generation:** Do not build a UI for this. The ASP system is a backend service. A Console Command (`make:command`) is the preferred way to generate/rotate these tokens.

### Project Structure Notes
- **Routes:** `routes/api.php` is the correct location.
- **Models:** `app/Models/User.php`.
- **Tests:** `tests/Feature/AspApiAuthTest.php`.

### References
- [Architecture: ASP API Security](_bmad-output/planning-artifacts/architecture.md#authentication--security)
- [Laravel 11 Sanctum Docs](https://laravel.com/docs/11.x/sanctum)

## Dev Agent Record

### Agent Model Used
Opencode (Grok 2.0 based)

### Debug Log References
- Git log indicates previous work on `PaymentController`. Ensure no regression in existing `web` auth while setting up `api` auth.

### Completion Notes List
- [x] Sanctum installed
- [x] API routes protected
- [x] Token generation command working
- [x] Tests passing

### File List
- `routes/api.php`
- `bootstrap/app.php`
- `app/Models/User.php`
- `app/Console/Commands/CreateAspToken.php`
- `app/Http/Controllers/Api/V1/AspSyncController.php`
- `tests/Feature/AspApiAuthTest.php`
- `tests/Feature/SanctumSetupTest.php`
- `tests/Feature/CreateAspTokenCommandTest.php`
- `config/sanctum.php`

## Senior Developer Review (AI)

- [x] Story file loaded from `{{story_path}}`
- [x] Story Status verified as reviewable (review)
- [x] Epic and Story IDs resolved (4.1)
- [x] Story Context located or warning recorded
- [x] Epic Tech Spec located or warning recorded
- [x] Architecture/standards docs loaded (as available)
- [x] Tech stack detected and documented
- [x] MCP doc search performed (or web fallback) and references captured
- [x] Acceptance Criteria cross-checked against implementation
- [x] File List reviewed and validated for completeness
- [x] Tests identified and mapped to ACs; gaps noted
- [x] Code quality review performed on changed files
- [x] Security review performed on changed files and dependencies
- [x] Outcome decided (Approve/Changes Requested/Blocked)
- [x] Review notes appended under "Senior Developer Review (AI)"
- [x] Change Log updated with review entry
- [x] Status updated according to settings (if enabled)
- [x] Sprint status synced (if sprint tracking enabled)
- [x] Story saved successfully

_Reviewer: Wavister on Wed Jan 21 2026_

### Review Notes

**Changes Applied during Review:**
1.  **Security Enhancement:** Updated `CreateAspToken` command to revoke existing tokens before creating a new one, preventing token accumulation.
2.  **Architecture Alignment:** Moved ASP Sync routes to `/api/v1/asp/ping` and implemented `AspSyncController` to replace closure-based routing.
3.  **Code Cleanup:** Removed redundant manual database cleanup in `CreateAspTokenCommandTest`.
4.  **Verification:** Updated tests to reflect the new v1 API path.

**Outcome:** APPROVED with auto-fixes applied.
