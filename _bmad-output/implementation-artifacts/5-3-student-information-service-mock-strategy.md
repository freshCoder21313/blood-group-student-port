# Story 5.3: Student Information Service (Mock Strategy)

Status: ready-for-dev

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

- [ ] **Define Contracts**
  - [ ] Create directory `app/Services/Student`.
  - [ ] Create Interface `app/Services/Student/StudentInformationServiceInterface.php`.
  - [ ] Define methods: `getGrades`, `getSchedule`, `getFees` with strict return types (array or Collection or DTO). *Recommendation: Use array or simple DTO for now.*

- [ ] **Implement Mock Service**
  - [ ] Create `app/Services/Student/MockStudentInformationService.php`.
  - [ ] Implement `getGrades`: Return sample data (Math 101, CS 102).
  - [ ] Implement `getSchedule`: Return sample data (Mon 9am, Tue 10am).
  - [ ] Implement `getFees`: Return sample data (Balance: 50,000, Status: Paid).

- [ ] **Service Provider Binding**
  - [ ] Modify `app/Providers/AppServiceProvider.php` (or create `app/Providers/StudentServiceProvider.php` if preferred, but App is fine).
  - [ ] Add logic to bind Interface to Implementation based on `config('services.student_info.driver')` or env.

- [ ] **Configuration**
  - [ ] Add `STUDENT_INFO_DRIVER=mock` to `.env.example`.
  - [ ] Add `student_info` config block to `config/services.php` (referencing env).

- [ ] **Tests**
  - [ ] Create `tests/Unit/Services/MockStudentInformationServiceTest.php`.
  - [ ] Test: methods return valid structure.

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

### File List
