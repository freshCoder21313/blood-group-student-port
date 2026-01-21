# Story 3.3: Final Submission Logic

Status: review

<!-- Note: Validation is optional. Run validate-create-story for quality check before dev-story. -->

## Story

As a Applicant,
I want to submit my completed application,
so that it can be reviewed by the university.

## Acceptance Criteria

1. **Given** I am on the Payment/Submission step
   **And** I have completed all required sections (Personal, Parent, Program, Documents)
   **And** my Payment status is 'paid' OR 'pending_verification'
   **When** I click "Submit Application"
   **Then** the system validates all application data against strict requirements
   **And** the Application status changes to 'submitted'
   **And** the `submitted_at` timestamp is recorded
   **And** I receive a confirmation email
   **And** I am redirected to the Dashboard

2. **Given** I have submitted my application
   **When** I view the Dashboard
   **Then** I see the status as "Submitted" (or "Under Review")
   **And** I can NO LONGER edit any part of my application (forms are read-only)

3. **Given** I have NOT completed all sections or Payment is pending/failed (and not manual pending)
   **When** I attempt to submit
   **Then** the "Submit" button is disabled OR clicking it shows specific validation errors

## Tasks / Subtasks

- [x] **Backend Implementation**
  - [x] Create `ApplicationSubmitted` Event
  - [x] Create `SendSubmissionConfirmation` Listener
  - [x] Create `SubmissionConfirmation` Mailable (Blade View)
  - [x] Update `ApplicationService::submit(Application $app)`
    - [x] Implement STRICT validation for all sections (Personal, Parent, Program, Docs)
    - [x] Validate Payment status (must be `paid` or `pending_verification`)
    - [x] Update status to `submitted` and set `submitted_at`
    - [x] Fire `ApplicationSubmitted` event
  - [x] Update `ApplicationController::submit` endpoint
  - [x] Update `ApplicationPolicy` to deny updates when status is `submitted`

- [x] **Frontend Implementation**
  - [x] Update `resources/views/application/payment.blade.php` (or relevant view)
    - [x] Ensure "Submit Application" button is visible when payment is complete/pending_verification
    - [x] Add loading state to button during submission
  - [x] Update Dashboard (`resources/views/dashboard.blade.php`) to reflect `submitted` status
  - [x] Ensure all form inputs are DISABLED/READ-ONLY when application is submitted (Middleware or Policy check in views)

- [x] **Testing**
  - [x] Feature Test: `ApplicationSubmissionTest`
    - [x] Test submission with valid data and payment -> Success, Email Sent, DB Updated
    - [x] Test submission with missing data -> Validation Error
    - [x] Test submission with pending/failed payment -> Error
    - [x] Test editing after submission -> 403 Forbidden
  - [x] Unit Test: `ApplicationService::submit`

## Dev Notes

- **Strict Validation:** Use `Validator::make($app->toArray(), $rules)` inside the service method to re-validate the entire application data before flipping the switch. This ensures no "draft" data (which bypassed validation) slips through.
- **Event/Listener:** Decouple the email sending using Laravel Events. This ensures the HTTP response is fast (especially if using queues later).
- **Policy:** The `EnsureApplicationIsDraft` middleware (mentioned in Architecture) or `ApplicationPolicy` update is critical to prevent post-submission edits.
- **Submission Button Location:** Based on Story 3.2, the button is likely in `payment.blade.php`. Ensure it's clear this is the *final* action.

### Project Structure Notes

- `app/Events/ApplicationSubmitted.php`: New
- `app/Listeners/SendSubmissionConfirmation.php`: New
- `app/Mail/SubmissionConfirmation.php`: New
- `resources/views/emails/applications/submitted.blade.php`: New
- `app/Services/Application/ApplicationService.php`: Modify
- `app/Http/Controllers/ApplicationController.php`: Modify
- `app/Policies/ApplicationPolicy.php`: Modify

### References

- [Source: _bmad-output/planning-artifacts/architecture.md#Data Architecture] (Draft vs Submitted State)
- [Source: _bmad-output/planning-artifacts/ux-design-specification.md#Journey 1] (Submission Flow)
- [Source: _bmad-output/implementation-artifacts/3-2-manual-payment-fallback.md] (Previous Story Context)

## Dev Agent Record

### Agent Model Used

{{agent_model_name_version}}

### Debug Log References

- Fixed regression in `ApplicationServicePaymentCheckTest` by adding required student/parent data for strict validation.
- Fixed `ApplicationSubmissionTest` forbidden check by ensuring request passed validation before hitting policy.
- Updated views to properly handle `$readonly` state using `ApplicationPolicy` 'view' (allowed) vs 'update' (denied).

### Completion Notes List

- Implemented `ApplicationSubmitted` event and listener for email confirmation.
- Updated `ApplicationService::submit` to use STRICT validation using `Validator` manually on `toArray()` data, loading all relationships.
- Updated `ApplicationFormController` to use `view` policy for GET requests and `update` policy for POST requests.
- Updated `ApplicationPolicy` to deny updates when status is `pending_approval` (Submitted) or `approved`.
- Updated `dashboard.blade.php` to show "Submitted - Under Review" status.
- Updated all form views (`personal`, `parent`, `program`, `documents`, `payment`) to be read-only (disabled inputs) when user cannot update application.
- Added loading state to Submit button in `payment.blade.php`.
- Verified flow with `ApplicationSubmissionTest`.

### File List

- app/Events/ApplicationSubmitted.php
- app/Listeners/SendSubmissionConfirmation.php
- app/Mail/SubmissionConfirmation.php
- resources/views/emails/applications/submitted.blade.php
- app/Services/Application/ApplicationService.php
- app/Http/Controllers/ApplicationFormController.php
- app/Policies/ApplicationPolicy.php
- resources/views/application/payment.blade.php
- resources/views/dashboard.blade.php
- resources/views/application/personal.blade.php
- resources/views/application/parent.blade.php
- resources/views/application/program.blade.php
- resources/views/application/documents.blade.php
- resources/views/components/image-uploader.blade.php
- tests/Unit/Events/ApplicationSubmittedTest.php
- tests/Unit/Listeners/SendSubmissionConfirmationTest.php
- tests/Unit/Mail/SubmissionConfirmationTest.php
- tests/Feature/ApplicationSubmissionTest.php
- tests/Unit/Services/ApplicationServiceSubmitTest.php
- tests/Feature/ApplicationPolicyTest.php
- tests/Unit/Services/ApplicationServicePaymentCheckTest.php
