# Story 4.2: Pending Applications Endpoint

Status: done

<!-- Note: Validation is optional. Run validate-create-story for quality check before dev-story. -->

## Story

As a System Integration,
I want to query for all 'Pending' applications,
So that I can import them into the internal ASP database.

## Acceptance Criteria

1. **Given** new applications have been submitted
2. **When** I GET `/api/v1/sync/pending`
3. **Then** I receive a JSON list of applications with status 'submitted'
4. **And** PII data is decrypted for the response
5. **And** the access is logged to `api_logs`

## Tasks / Subtasks

- [x] Create API Resources
    - [x] Create `ApplicationResource` (Main wrapper)
    - [x] Create `StudentResource` (Includes decrypted PII)
    - [x] Create `DocumentResource` (Includes download links)
- [x] Implement Controller Logic
    - [x] Update `AspSyncController::pending` method
    - [x] Query `Application` where `status = 'submitted'`
    - [x] Eager load relationships (`student`, `program`, `documents`, `payment`)
    - [x] Return `ApplicationResource::collection`
- [x] Implement Logging
    - [x] Log the request to `api_logs` table (IP, Endpoint, Status, Timestamp)
    - [x] Ensure logging happens even if logic fails (try/catch or middleware)
- [x] Configure Routes
    - [x] Add `GET /sync/pending` to `routes/api.php` inside the Sanctum `asp` group
- [x] Verification Tests
    - [x] Create `tests/Feature/AspSyncPendingTest.php`
    - [x] Test: Unauthenticated -> 401
    - [x] Test: Wrong Ability -> 403
    - [x] Test: Success -> 200 with correct JSON structure
    - [x] Test: PII is present in response
    - [x] Test: API Log created

## Dev Notes

### Architecture Compliance

-   **API Pattern:** Use **Eloquent API Resources** (`JsonResource`) for all outputs. Do NOT return raw models.
-   **Security:** Ensure PII (National ID, Passport) is visible in the JSON response (decrypted) because the ASP system needs the raw data to process the admission. The `Student` model likely handles decryption via accessors, but you must ensure the Resource calls them.
-   **Logging:** Use the existing `ApiLog` model. Log the *client* (ASP) access, not just internal errors.
-   **Structure:**
    -   Controller: `app/Http/Controllers/Api/V1/AspSyncController.php`
    -   Resources: `app/Http/Resources/V1/`
    -   Tests: `tests/Feature/AspSyncPendingTest.php`

### Implementation Specifics

-   **Route:** The previous story set up the group `prefix('asp')`. The requirement is `GET /api/v1/sync/pending`. You may need to adjust the group nesting or add a new group to achieve this exact path, OR stick to the existing pattern `GET /api/v1/asp/pending` and update the ACs if that's more consistent. **Decision:** Stick to the Story requirement `GET /api/v1/sync/pending`. You might need a new group `prefix('sync')` protected by the same middleware.
-   **Status:** The application status for "Pending" in ASP corresponds to `'submitted'` in the Portal database.
-   **Eager Loading:** Be careful with N+1 queries. Use `with(['student', 'program', 'documents'])`.

### Project Structure Notes

-   **Resources:** Place in `app/Http/Resources/V1/`.
-   **Models:** `Application`, `Student`, `ApiLog` already exist.

### References

-   [Architecture: API Communication Patterns](_bmad-output/planning-artifacts/architecture.md#api--communication-patterns)
-   [Larvel 11 API Resources](https://laravel.com/docs/11.x/eloquent-resources)

## Dev Agent Record

### Agent Model Used
Opencode (Grok 2.0 based)

### Debug Log References
-   Existing `AspSyncController` has a `ping` method. You will expand this class.

### Completion Notes List
-   [x] Resources created
-   [x] Controller updated
-   [x] Logging implemented
-   [x] Tests passed

### Implementation Notes
-   Mapped 'submitted' status to 'pending_approval' in DB as 'submitted' is not a valid enum value.
-   Implemented logging using try/finally block in controller.
-   Added `tests/Unit/Resources/ResourceTest.php` for unit testing resources.

### File List
-   `app/Http/Controllers/Api/V1/AspSyncController.php`
-   `app/Http/Resources/V1/ApplicationResource.php`
-   `app/Http/Resources/V1/StudentResource.php`
-   `app/Http/Resources/V1/DocumentResource.php`
-   `routes/api.php`
-   `tests/Feature/AspSyncPendingTest.php`
-   `tests/Unit/Resources/ResourceTest.php`
-   `database/factories/DocumentFactory.php`

## Senior Developer Review (AI)

_Reviewer: Wavister on Wed Jan 21 2026_

### Findings & Fixes
-   **CRITICAL:** Status mismatch. Fixed `ApplicationResource` to map `pending_approval` -> `submitted` to meet ACs.
-   **CRITICAL:** Missing pagination. Updated `AspSyncController` to use `paginate(50)`.
-   **MEDIUM:** Useless document path. Updated `DocumentResource` to include `download_url`.
-   **MEDIUM:** Over-fetching. Removed unused eager loading in Controller.
-   **LOW:** Missing Factory. Created `DocumentFactory.php` and updated unit tests.

### Outcome
**APPROVED**. All issues resolved automatically.
