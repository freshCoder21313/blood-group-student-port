# Story 5.3: Student Information Service (Mock Strategy)

Status: review

<!-- Note: Validation is optional. Run validate-create-story for quality check before dev-story. -->

## Story

As a Developer,
I want a decoupled service for fetching student data,
So that I can build the UI now using Mock data and swap to Realtime ASP API later.

## Acceptance Criteria

1.  **Service Interface:**
    - **Given** I need to fetch student data (grades, schedule, fees)
    - **When** I type-hint `StudentInformationServiceInterface`
    - **Then** I receive a contract defining the required methods.
    - **Methods Required:** `getGrades(string $studentCode)`, `getSchedule(string $studentCode)`, `getFees(string $studentCode)`.

2.  **Mock Implementation:**
    - **Given** the environment is set to use the Mock driver
    - **When** I call the service methods
    - **Then** I receive structured, hardcoded JSON data suitable for UI development.
    - **Data Structure:**
        - `getGrades`: Returns collection of courses with codes, names, and grades.
        - `getSchedule`: Returns collection of classes with times, locations, and units.
        - `getFees`: Returns balance, invoice history, and status.

3.  **Service Binding & Configuration:**
    - **Given** the `.env` file has `STUDENT_INFO_DRIVER=mock`
    - **When** the application boots
    - **Then** `App\Services\Student\StudentInformationServiceInterface` is bound to `App\Services\Student\MockStudentInformationService`.
    - **And** if `STUDENT_INFO_DRIVER=asp` (future), it would bind to a different implementation (out of scope, but binding logic must support it).

4.  **Testing:**
    - **Given** a Unit Test
    - **When** I instantiate the Mock Service
    - **Then** it returns the expected sample data consistently.

## Tasks / Subtasks

- [x] **Define Contracts**
  - [x] Create directory `app/Services/Student`.
  - [x] Create Interface `app/Services/Student/StudentInformationServiceInterface.php`.
  - [x] Define methods: `getGrades`, `getSchedule`, `getFees` with strict return types (array or Collection or DTO). *Recommendation: Use array or simple DTO for now.*

- [x] **Implement Mock Service**
  - [x] Create `app/Services/Student/MockStudentInformationService.php`.
  - [x] Implement `getGrades`: Return sample data (Math 101, CS 102).
  - [x] Implement `getSchedule`: Return sample data (Mon 9am, Tue 10am).
  - [x] Implement `getFees`: Return sample data (Balance: 50,000, Status: Paid).

- [x] **Service Provider Binding**
  - [x] Modify `app/Providers/AppServiceProvider.php` (or create `app/Providers/StudentServiceProvider.php` if preferred, but App is fine).
  - [x] Add logic to bind Interface to Implementation based on `config('services.student_info.driver')` or env.

- [x] **Configuration**
  - [x] Add `STUDENT_INFO_DRIVER=mock` to `.env.example`.
  - [x] Add `student_info` config block to `config/services.php` (referencing env).

- [x] **Tests**
  - [x] Create `tests/Unit/Services/MockStudentInformationServiceTest.php`.
  - [x] Test: methods return valid structure.

## Dev Notes

- **Architecture Patterns:**
  - **Strategy Pattern:** This is a classic implementation of the Strategy Pattern to decouple the UI from the Data Source.
  - **Namespace:** `App\Services\Student`.
  - **Type Safety:** Use `declare(strict_types=1);`.

- **Data Structure Recommendations (Mock):**
  - **Grades:** `[['code' => 'CS101', 'name' => 'Intro to CS', 'grade' => 'A'], ...]`
  - **Schedule:** `[['day' => 'Monday', 'time' => '09:00', 'course' => 'CS101', 'venue' => 'Room A'], ...]`
  - **Fees:** `['balance' => 50000, 'currency' => 'KES', 'status' => 'Pending']`

- **Project Structure Alignment:**
  - Follows `app/Services` pattern defined in `architecture.md`.
  - Prepares the ground for `Story 5.4` which will consume this service.

### References

- **Architecture:** `_bmad-output/planning-artifacts/architecture.md` (Service Layer).
- **Epics:** `_bmad-output/planning-artifacts/epics.md` (Story 5.3).

## Dev Agent Record

### Agent Model Used

opencode (Manual)

### Debug Log References

### Completion Notes List

- Implemented `StudentInformationServiceInterface` and `MockStudentInformationService`.
- Configured dynamic binding in `AppServiceProvider`.
- Added unit tests for Mock Service and Service Binding.
- Fixed a regression in `ApiAuthenticationTest` due to mismatched config keys.
- **Code Review Update:** Added `invoice_history` to `MockStudentInformationService` (AC #2 fix).
- **Code Review Update:** Hardened `MockStudentInformationServiceTest` to verify data types and non-empty arrays.

### File List

- student-admission-portal/app/Services/Student/StudentInformationServiceInterface.php
- student-admission-portal/app/Services/Student/MockStudentInformationService.php
- student-admission-portal/app/Providers/AppServiceProvider.php
- student-admission-portal/config/services.php
- student-admission-portal/.env.example
- student-admission-portal/tests/Unit/Services/MockStudentInformationServiceTest.php
- student-admission-portal/tests/Unit/Services/StudentServiceBindingTest.php
- student-admission-portal/tests/Unit/Middleware/ApiAuthenticationTest.php

## Senior Developer Review (AI)

- [x] Story file loaded from `_bmad-output/implementation-artifacts/5-3-student-information-service-mock-strategy.md`
- [x] Story Status verified as reviewable (review)
- [x] Epic and Story IDs resolved (5.3)
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
- [x] Outcome decided (Approve)
- [x] Review notes appended under "Senior Developer Review (AI)"
- [x] Change Log updated with review entry
- [x] Status updated according to settings (if enabled)
- [x] Sprint status synced (if sprint tracking enabled)
- [x] Story saved successfully

_Reviewer: Wavister on Thu Jan 22 2026_

### Review Notes
- **Fixed:** Added missing `invoice_history` to `MockStudentInformationService`.
- **Fixed:** Improved test robustness in `MockStudentInformationServiceTest`.
- **Note:** Service Interface uses array return types; consider DTOs for future refactoring.
