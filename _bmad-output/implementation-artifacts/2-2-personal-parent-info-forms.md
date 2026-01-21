# Story 2.2: Personal & Parent Info Forms

Status: done

<!-- Note: Validation is optional. Run validate-create-story for quality check before dev-story. -->

## Story

As a Applicant,
I want to fill in my personal and parent details,
so that the university has my contact and background information.

## Acceptance Criteria

1. **Given** I am on the dashboard
   **When** I click the "Personal Information" card
   **Then** I am taken to the Personal Info form
   **And** the form is pre-filled with any existing data (e.g. from registration)

2. **Given** I am on the Personal Info form
   **When** I enter my National ID, DOB, and other details and click "Save"
   **Then** the data is saved to the `students` table
   **And** sensitive fields (National ID, Passport) are encrypted at rest
   **And** if I click "Next", I am taken to the Parent Info form

3. **Given** I am on the Parent Info form
   **When** I enter Parent/Guardian details (Name, Phone, Relationship) and click "Save"
   **Then** the data is saved to the `parent_info` table linked to my student record
   **And** I am returned to the Dashboard (or taken to next step if "Next" clicked)

4. **Given** I am editing an application in "Draft" status
   **When** I leave required fields empty and click "Save Draft"
   **Then** the system saves the partial data without validation errors

5. **Given** I try to "Submit" the application later (in a future story)
   **Then** strict validation will ensure all required fields from these forms are present
   (Note: For this story, focus on allowing partial save)

## Tasks / Subtasks

- [x] Database Schema
  - [x] Create migration for `parent_info` table (cols: `student_id`, `guardian_name`, `guardian_phone`, `relationship`, `guardian_email`)
  - [x] Ensure `students` table has all required columns (`dob`, `gender`, `address`, `county`) - update/add migration if missing from Story 1.2
  - [x] Configure `Student` and `ParentInfo` models with relationships (`Student` hasOne `ParentInfo`)

- [x] Backend Implementation
  - [x] Update `ApplicationService` with methods: `updatePersonalDetails` and `updateParentDetails`
  - [x] Implement `PersonalDetailsRequest` and `ParentDetailsRequest` (with "sometimes" rules for Draft mode)
  - [x] Update `ApplicationController` (or create `ApplicationFormController`) to handle these form steps

- [x] Frontend Implementation (Blade)
  - [x] Create `resources/views/application/personal.blade.php` using `x-card` and `x-input`
  - [x] Create `resources/views/application/parent.blade.php` using `x-card` and `x-input`
  - [x] Update Dashboard cards to link to these routes
  - [x] Implement "Save" and "Save & Next" buttons

- [x] Security & Compliance
  - [x] Verify `national_id` and `passport_number` are using `encrypted` cast in `Student` model
  - [x] Ensure `parent_info` data is sanitized

- [x] Testing
  - [x] Feature Test: `StudentCanUpdatePersonalDetailsTest` (Happy path + Draft path)
  - [x] Feature Test: `StudentCanUpdateParentDetailsTest`
  - [x] Unit Test: `ParentInfo` model relationship

## Dev Notes

- **Architecture Pattern:** Follow the Service Layer pattern strictly. Do NOT put logic in Controllers.
  - `ApplicationController` should call `ApplicationService::updatePersonalDetails()` and `ApplicationService::updateParentDetails()`.
- **Validation Strategy:** Use Form Requests (`PersonalDetailsRequest`, `ParentDetailsRequest`).
  - **Critical:** Use the `sometimes` rule or conditionally apply `required` rules based on the `Application::status`. If status is 'draft', fields can be nullable. If 'submitted', they are required.
- **Frontend:** Use the existing `x-card` and `x-input` components. Ensure the "App Dashboard" look and feel is maintained (no sidebars).

### Project Structure Notes

- **Models:**
  - `app/Models/Student.php`: Ensure `casts` array includes `'national_id' => 'encrypted'` and `'passport_number' => 'encrypted'`.
  - `app/Models/ParentInfo.php`: Create this model. Relationships: `Student` hasOne `ParentInfo`.
- **Migrations:**
  - Check `database/migrations` for existing `students` table.
  - Create new migration for `parent_infos` table.
- **Routes:**
  - `GET /application/{application}/personal`
  - `POST /application/{application}/personal`
  - `GET /application/{application}/parent`
  - `POST /application/{application}/parent`

### References

- [Source: _bmad-output/planning-artifacts/architecture.md#Authentication & Security] (PII Encryption)
- [Source: _bmad-output/planning-artifacts/ux-design-specification.md#Journey 1: Registration & Application] (Form flow)
- [Source: _bmad-output/implementation-artifacts/2-1-dashboard-application-initialization.md] (Previous story context)

## Dev Agent Record

### Agent Model Used

{{agent_model_name_version}}

### Debug Log References

- Checked for `parent_info` migration: Not found. Added to tasks.
- Verified PII requirements: `national_id` and `passport_number` must be encrypted.

### Completion Notes List

- Implemented database schema updates for `parent_info` table (renamed columns to match requirements).
- Created `ApplicationFormController` with methods `personal`, `updatePersonal`, `parent`, `updateParent`.
- Implemented `PersonalDetailsRequest` and `ParentDetailsRequest` with "sometimes" rules for Draft application status.
- Updated `ApplicationService` with dedicated methods `updatePersonalDetails` and `updateParentDetails`.
- Created Blade views `personal.blade.php` and `parent.blade.php` using existing UI components.
- Updated Dashboard to link to form steps correctly.
- Wrote unit and feature tests covering form display, updates, and navigation logic.
- Resolved configuration issue with `AspApiService` causing test failures.
- Fixed 404 error in tests caused by missing `ApplicationStep` records in factories by using `ApplicationService` for test data setup.

### File List

- student-admission-portal/database/migrations/2026_01_21_032658_update_parent_info_columns.php
- student-admission-portal/app/Models/ParentInfo.php
- student-admission-portal/app/Http/Requests/PersonalDetailsRequest.php
- student-admission-portal/app/Http/Requests/ParentDetailsRequest.php
- student-admission-portal/app/Services/Application/ApplicationService.php
- student-admission-portal/app/Http/Controllers/ApplicationFormController.php
- student-admission-portal/resources/views/application/personal.blade.php
- student-admission-portal/resources/views/application/parent.blade.php
- student-admission-portal/resources/views/dashboard.blade.php
- student-admission-portal/routes/web.php
- student-admission-portal/tests/Unit/ParentInfoTest.php
- student-admission-portal/tests/Feature/StudentCanUpdatePersonalDetailsTest.php
- student-admission-portal/tests/Feature/StudentCanUpdateParentDetailsTest.php
- student-admission-portal/config/asp_integration.php
- student-admission-portal/app/Http/Controllers/DashboardController.php
- student-admission-portal/tests/Feature/ApplicationInitializationTest.php

## Senior Developer Review (AI)

**Date:** 2026-01-21
**Reviewer:** Wavister (AI)
**Status:** Approved

**Findings & Fixes:**
- **Security:** Enabled `otp.verified` middleware in `web.php` which was previously commented out.
- **Security/Logic:** Implemented `ApplicationPolicy` and updated `ApplicationFormController` to enforce strict authorization checks using `$this->authorize('update', $application)`.
- **Validation:** Tightened `date_of_birth` validation to ensure applicants are at least 16 years old.
- **Testing:** Improved `StudentCanUpdateParentDetailsTest` to strictly assert redirection back to the form context.

**Verification:**
- All features function as per ACs.
- Tests passed (6 tests, 16 assertions).
