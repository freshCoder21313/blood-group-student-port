# Story 2.1: Dashboard & Application Initialization

Status: done

<!-- Note: Validation is optional. Run validate-create-story for quality check before dev-story. -->

## Senior Developer Review (AI)

### Outcome: Approved (Auto-Fixed)

The following issues were identified and automatically fixed:

1.  **UX Violations (High)**:
    -   `status-badge.blade.php`: Updated colors to match UX Spec (Draft = Amber, Request Info = Orange, Approved = Emerald).
    -   `dashboard.blade.php`: Replaced Emojis with SVG Heroicons.

2.  **Security (High)**:
    -   `DashboardController`: Added explicit `$this->authorize('create', Application::class)` check in `store` method.

3.  **Data Integrity (Medium)**:
    -   `ApplicationService`: Refactored to use `null` for missing student data instead of empty strings.
    -   **Migration Created**: `2026_01_21_024521_make_student_fields_nullable.php` to support partial student profiles during draft creation.

4.  **Implementation Gaps (Medium)**:
    -   Created `application/wizard.blade.php` to provide a valid destination for the "Apply Now" redirect.
    -   Updated `web.php` route to return the view.

5.  **Test Quality (Medium)**:
    -   Updated `DashboardTest` to assert UI text content ("Apply Now", "Continue Application") instead of just variable presence.

6.  **Test Quality (High) - Review 2**:
    -   Refactored `ApplicationInitializationTest.php` to be a true HTTP Feature Test covering the critical `POST /applications` endpoint (Controller -> Service -> DB).
    -   Previously it was only testing the Service class directly.

7.  **Code Maintainability (Low) - Review 2**:
    -   Updated `DashboardTest.php` to use `ApplicationFactory` for cleaner, more robust test setup.

## Story

As a Applicant,
I want to start a new application from my dashboard,
so that I can begin the admission process.

## Acceptance Criteria

1. **Given** I am logged in
   **When** I click "Apply Now" on the dashboard
   **Then** a new record is created in `applications` table with status 'draft'
   **And** I am redirected to the first step of the application wizard
   **And** I can see my application status on the dashboard

2. **Given** I have an existing draft application
   **When** I visit the dashboard
   **Then** I see a "Continue Application" button instead of "Apply Now"
   **And** the status badge shows "Draft"

3. **Given** I am on the dashboard
   **Then** I see the 4-card overview (Personal, Parent, Program, Documents) as per UX design
   **And** the progress bar reflects my current completion status

## Tasks / Subtasks

- [x] Database Schema Updates
  - [x] Create migration to make `program_id` and `block_id` nullable in `applications` table (Critical for 'Draft' status)
  - [x] Ensure `status` column exists with default 'draft'

- [x] Backend Implementation
  - [x] Update `ApplicationService::createDraft()` to handle partial creation
  - [x] Update `DashboardController` to fetch current application status
  - [x] Create `ApplicationPolicy` to authorize viewing/creating applications

- [x] Frontend Implementation (Blade Components)
  - [x] Create `x-card` component for dashboard items
  - [x] Create `x-status-badge` component
  - [x] Implement `dashboard.blade.php` using the "App Dashboard" card-based layout
  - [x] Implement "Apply Now" / "Continue" logic in the view

- [x] Testing
  - [x] Write Feature test `ApplicationInitializationTest` (Happy Path: Create Draft)
  - [x] Write Unit test for `ApplicationService` status logic

## Dev Notes

- **Critical Architecture Decision:** The `applications` table MUST support nullable columns (`program_id`, `block_id`) to allow the "Draft" state. The standard `ApplicationService` likely enforces strict validation - you must relax this for `status = draft`.
- **UX Direction:** This is the "App Dashboard" direction. Do not build a sidebar. Build a grid of 4 cards.
- **Tailwind v4:** Ensure you use the v4 configuration pattern if modifying themes (CSS-based config).

### Project Structure Notes

- **Services:** Logic for creating the draft must be in `app/Services/ApplicationService.php`.
- **Controllers:** `DashboardController` should only call the service and return the view.
- **Views:** Use `resources/views/dashboard.blade.php`.
- **Naming:** Follow `snake_case` for DB, `kebab-case` for URLs.

### References

- [Source: _bmad-output/planning-artifacts/architecture.md#Decision Priority Analysis] (Nullable columns decision)
- [Source: _bmad-output/planning-artifacts/ux-design-specification.md#Design Direction Decision] (Dashboard Layout)
- [Source: _bmad-output/project-context.md#Critical Implementation Rules] (Service Pattern, Strict Types)

## Dev Agent Record

### Agent Model Used

opencode (interactive-session)

### Debug Log References

- Verified `package.json` for Tailwind v4.
- Confirmed `sprint-status.yaml` update for Epic 2.

### Completion Notes List

- Updated sprint status for Epic 2 to 'in-progress'.
- Identified critical schema change requirement (nullable columns).
- Implemented `createDraft` in `ApplicationService` with strict types.
- Created `DashboardController` and `ApplicationPolicy`.
- Implemented 4-card layout in `dashboard.blade.php`.
- Added Feature and Unit tests, all passing.

### File List

- app/Http/Controllers/DashboardController.php
- app/Policies/ApplicationPolicy.php
- app/Services/Application/ApplicationService.php
- database/factories/ApplicationFactory.php
- database/migrations/2026_01_21_021842_make_application_fields_nullable.php
- database/migrations/2026_01_21_024521_make_student_fields_nullable.php
- resources/views/application/wizard.blade.php
- resources/views/components/card.blade.php
- resources/views/components/status-badge.blade.php
- resources/views/dashboard.blade.php
- routes/web.php
- tests/Feature/ApplicationInitializationTest.php
- tests/Feature/ApplicationPolicyTest.php
- tests/Feature/DashboardTest.php
- tests/Unit/Services/ApplicationServiceTest.php
