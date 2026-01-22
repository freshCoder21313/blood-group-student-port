# Story 4.4: Sync Resilience & Logging

Status: done

<!-- Note: Validation is optional. Run validate-create-story for quality check before dev-story. -->

## Story

As a Auditor,
I want a full log of all sync activities,
So that I can troubleshoot failures and track data movement.

## Acceptance Criteria

1. **Given** any sync API request (Success or Fail)
2. **When** the request completes
3. **Then** a record is written to `api_logs` with timestamp, IP, endpoint, and response code
4. **And** status changes are recorded in `status_histories`

## Tasks / Subtasks

- [x] Database Schema Verification
  - [x] Verify `api_logs` table exists (columns: `ip_address`, `method`, `endpoint`, `response_code`, `duration_ms`, `payload` (optional), `response` (optional))
  - [x] Verify `status_histories` table exists (columns: `application_id`, `from_status`, `to_status`, `comment`)
  - [x] Create migration if tables are missing or incomplete
- [x] Implement Logging Middleware
  - [x] Create `app/Http/Middleware/LogApiRequests.php`
  - [x] Implement logic to capture request/response details
  - [x] Handle exception logging (ensure failed requests are logged with 500/4xx codes)
  - [x] **Security:** Ensure PII (e.g., in payloads) is NOT logged or is masked
- [x] Apply Middleware
  - [x] Register middleware in `bootstrap/app.php` (Laravel 11)
  - [x] Apply to `api` routes group or specifically to `/api/v1/sync/*` routes
  - [x] **Refactor:** Remove ad-hoc logging from `AspSyncController` (implemented in Stories 4.2/4.3) to avoid duplicate logs
- [x] Status History Resilience
  - [x] Review `ApplicationService` to ensure `status_histories` is written *inside* the DB transaction
  - [x] Ensure all status transitions (Draft -> Submitted, Submitted -> Approved/Rejected) are recorded
- [x] Resilience / Error Handling
  - [x] Ensure API returns consistent JSON error structures even for unhandled exceptions (Handler.php)
  - [x] Verify 401/403 errors (Sanctum) are also logged by the middleware
- [x] Testing
  - [x] Create `tests/Feature/SyncLoggingTest.php`
  - [x] Test: Successful Sync -> Log created
  - [x] Test: Failed Sync (401/422/500) -> Log created with correct error code
  - [x] Test: Status Change -> History record created
  - [x] Test: PII Masking (if applicable)

## Dev Notes

### Architecture Compliance

-   **Middleware Pattern:** Use Middleware for cross-cutting concerns like logging. Do not pollute Controllers with logging logic.
-   **Service Pattern:** Status history writing must remain in `ApplicationService`.
-   **Laravel 11:** Use the new Middleware registration in `bootstrap/app.php`.

### Technical specifics

-   **`api_logs` Schema Suggestion:**
    -   `id` (BigInt)
    -   `ip_address` (String)
    -   `method` (String)
    -   `url` (String)
    -   `status_code` (Int)
    -   `duration_ms` (Int)
    -   `request_body` (Json/Text - nullable, masked)
    -   `response_body` (Json/Text - nullable, truncated)
    -   `created_at` (Timestamp)
-   **`status_histories` Schema Suggestion:**
    -   `id` (BigInt)
    -   `application_id` (FK)
    -   `from_status` (String/Enum)
    -   `to_status` (String/Enum)
    -   `comment` (Text - nullable)
    -   `created_at` (Timestamp)
    -   `user_id` (Nullable - for who triggered it, e.g., ASP API User)

### Previous Story Intelligence

-   **Refactoring Warning:** Stories 4.2 and 4.3 implemented ad-hoc logging in `AspSyncController`. You MUST refactor this to use the new Middleware to prevent double logging or code duplication. Check `AspSyncController::pending` and `updateStatus`.
-   **Service Location:** Use `app/Services/Application/ApplicationService.php` (not `app/Services/ApplicationService.php`).

### Project Structure Notes

-   **Middleware:** `app/Http/Middleware/`
-   **Migrations:** `database/migrations/`
-   **Tests:** `tests/Feature/`

### References

-   [Architecture: Cross-Cutting Concerns](_bmad-output/planning-artifacts/architecture.md#cross-cutting-concerns-identified)
-   [Story 4.3: Status Update Endpoint](_bmad-output/implementation-artifacts/4-3-status-update-endpoint.md)

## Dev Agent Record

### Agent Model Used

Opencode-Standard-Agent

### Debug Log References

- Fixed schema issue by renaming `notes` to `comment` in `status_histories`.
- Implemented `LogApiRequests` middleware to handle comprehensive logging.
- Refactored `AspSyncController` to remove ad-hoc logging.
- Updated `ApplicationService` to log status changes on submission.
- Fixed existing test `AspSyncStatusUpdateTest.php` to align with `comment` column rename.
- **Review Fix:** Added request body truncation (max 10kb) to `LogApiRequests.php` to prevent DoS/Storage issues.
- **Review Fix:** Added missing PII masking verification test to `SyncLoggingTest.php`.

### Completion Notes List

- Database schema updated: `status_histories` now uses `comment` column.
- Middleware applied globally to `api` routes, ensuring all API interactions (including failures) are logged.
- PII masking implemented for sensitive fields.
- Tests passed for middleware logic and status history verification.

### File List

- student-admission-portal/database/migrations/2026_01_22_001300_update_status_histories_rename_notes_to_comment.php
- student-admission-portal/app/Models/StatusHistory.php
- student-admission-portal/app/Services/Application/ApplicationService.php
- student-admission-portal/app/Models/ApiLog.php
- student-admission-portal/app/Http/Middleware/LogApiRequests.php
- student-admission-portal/bootstrap/app.php
- student-admission-portal/app/Http/Controllers/Api/V1/AspSyncController.php
- student-admission-portal/tests/Feature/SyncLoggingTest.php
- student-admission-portal/tests/Feature/AspSyncStatusUpdateTest.php
