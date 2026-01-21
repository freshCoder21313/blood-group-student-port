# Story 4.3: Status Update Endpoint

Status: done

<!-- Note: Validation is optional. Run validate-create-story for quality check before dev-story. -->

## Story

As a System Integration,
I want to push status updates (Approved/Rejected) back to the portal,
So that the student sees the latest decision.

## Acceptance Criteria

1. **Given** an admission decision is made in ASP
2. **When** I POST to `/api/v1/sync/status` with `{ "application_id": 1, "status": "approved" }`
3. **Then** the local application status is updated
4. **And** the student receives an email notification via the existing notification service
5. **And** the status change is recorded in `status_histories`
6. **And** the request is logged in `api_logs`

## Tasks / Subtasks

- [x] Create Request Validation
  - [x] Create `app/Http/Requests/Api/V1/UpdateApplicationStatusRequest.php`
  - [x] Validate `application_id` exists in `applications` table
  - [x] Validate `status` is a valid enum value (`approved`, `rejected`, `request_info`)
- [x] Implement Service Logic
  - [x] Update `ApplicationService::updateStatus` (or create if missing)
  - [x] Verify valid state transition (e.g., can only approve if currently `pending_approval`)
  - [x] Update `applications` table `status`
  - [x] Create record in `status_histories` table
  - [x] Fire `ApplicationStatusChanged` event
- [x] Implement Controller Logic
  - [x] Add `updateStatus` method to `app/Http/Controllers/Api/V1/AspSyncController.php`
  - [x] Use `UpdateApplicationStatusRequest` for validation
  - [x] Call `ApplicationService` to perform the update
  - [x] Log the request to `api_logs` (reusing 4.2 pattern or middleware)
  - [x] Return updated `ApplicationResource`
- [x] Implement Notification System
  - [x] Create `app/Events/ApplicationStatusChanged.php`
  - [x] Create `app/Listeners/SendStatusChangeEmail.php`
  - [x] Create `app/Mail/ApplicationStatusUpdated.php` (Blade view for email)
  - [x] Register Event/Listener in `EventServiceProvider` (or auto-discovery)
- [x] Verification Tests
  - [x] Create `tests/Feature/AspSyncStatusUpdateTest.php`
  - [x] Test: Unauthenticated -> 401
  - [x] Test: Invalid Status -> 422
  - [x] Test: Application Not Found -> 404/422
  - [x] Test: Success (Approved) -> 200 + DB Updated + History Created + Email Sent
  - [x] Test: Success (Rejected) -> 200 + DB Updated + Email Sent

## Dev Notes

### Architecture Compliance

-   **Service Pattern:** All business logic (updating DB, writing history, firing events) MUST be in `ApplicationService`. The controller should only validate and call the service.
-   **Events:** Use Laravel Events for side effects like Email. Do not send email directly in the Service or Controller.
-   **API Resources:** Return the `ApplicationResource` (created in Story 4.2) for the response.
-   **Validation:** Use a dedicated `FormRequest` class.
-   **Logging:** Ensure `api_logs` are written. Consider if the logging logic from 4.2 can be reused or if it needs to be copied.

### Technical specifics

-   **Status Enums:**
    -   Based on Story 4.2 findings, the status in DB for "Pending" is `pending_approval`.
    -   Expected input status from ASP: `approved`, `rejected`, `request_info`.
    -   Ensure validation rules match the database enum definition.
-   **Status History:**
    -   Table `status_histories` likely exists (check migration if needed, or create if missing - derived from AC 5). Check `data-models-student-admission-portal.md` if available, or assume standard structure (`application_id`, `from_status`, `to_status`, `comment`, `created_at`).
-   **Routes:**
    -   Add `POST /sync/status` to the existing `asp` (or `sync`) group in `routes/api.php`.

### Project Structure Notes

-   **Events:** `app/Events/`
-   **Listeners:** `app/Listeners/`
-   **Mail:** `app/Mail/`
-   **Requests:** `app/Http/Requests/Api/V1/`

### References

-   [Architecture: Data Architecture](_bmad-output/planning-artifacts/architecture.md#data-architecture)
-   [Story 4.2: Pending Applications Endpoint](_bmad-output/implementation-artifacts/4-2-pending-applications-endpoint.md)

## Dev Agent Record

### Agent Model Used

Claude 3.5 Sonnet

### Debug Log References

### Completion Notes List

- Implemented `updateStatus` in `ApplicationService` with DB transactions, history logging, and event dispatching.
- Implemented `updateStatus` in `AspSyncController` with API logging.
- Implemented `SendStatusChangeEmail` listener and `ApplicationStatusUpdated` mailable using Laravel's auto-discovery.
- Verified with Unit and Feature tests, covering success paths, error handling, and email sending.
- [AI Review] Removed duplicate `ApplicationService.php`.
- [AI Review] Added `comment` support for status updates.
- [AI Review] Added warning logging for missing email in listener.

### File List

- student-admission-portal/app/Http/Requests/Api/V1/UpdateApplicationStatusRequest.php
- student-admission-portal/app/Http/Controllers/Api/V1/AspSyncController.php
- student-admission-portal/routes/api.php
- student-admission-portal/tests/Feature/AspSyncStatusUpdateTest.php
- student-admission-portal/app/Services/Application/ApplicationService.php
- student-admission-portal/app/Mail/ApplicationStatusUpdated.php
- student-admission-portal/app/Listeners/SendStatusChangeEmail.php
- student-admission-portal/resources/views/emails/application/status-updated.blade.php
- student-admission-portal/tests/Unit/ApplicationServiceStatusUpdateTest.php
- student-admission-portal/tests/Unit/SendStatusChangeEmailTest.php
- student-admission-portal/app/Events/ApplicationStatusChanged.php

## Senior Developer Review (AI)

- [x] Story file loaded from `_bmad-output/implementation-artifacts/4-3-status-update-endpoint.md`
- [x] Story Status verified as reviewable (review)
- [x] Epic and Story IDs resolved (4.3)
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

_Reviewer: Wavister on Thu Jan 22 2026_

### Review Findings

**Issues Fixed:**
- **Duplicate Service File**: Deleted `student-admission-portal/app/Services/ApplicationService.php`.
- **Missing Reason Support**: Added `comment` support to `UpdateApplicationStatusRequest`, `AspSyncController`, and `ApplicationService`. Added test case.
- **Silent Email Failure**: Added logging to `SendStatusChangeEmail` when user/email is missing.
- **Documentation**: Updated File List in story.

**Status**: Approved (Automatic Fixes Applied)
