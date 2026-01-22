# Story 5.2: Student Dashboard Transformation

Status: done

<!-- Note: Validation is optional. Run validate-create-story for quality check before dev-story. -->

## Story

As a Approved Student,
I want to see a dedicated Student Dashboard instead of the Admission Status,
So that I can access my student services.

## Acceptance Criteria

1.  **Dashboard Redirection:**
    - **Given** I am a logged-in user
    - **And** my application status is `approved`
    - **When** I visit `/dashboard`
    - **Then** I am shown the **Student Dashboard View** instead of the Applicant View.
    - **And** if my application status is NOT `approved` (e.g., `draft`, `submitted`, `pending_approval`), I see the standard Applicant View.

2.  **Student Dashboard View:**
    - **Display:** A "Welcome, [Student Name]" message.
    - **Display:** "Student ID: [student_code]" (e.g., "Student ID: STD-2024-001").
    - **Layout:** Use the card-based layout (`x-card`) consistent with the design system.

3.  **Navigation Links:**
    - Display cards/links for the following services:
        - **My Grades** (Link to `/student/grades`)
        - **Class Schedule** (Link to `/student/schedule`)
        - **Fee Statement** (Link to `/student/fees`)

4.  **Integration:**
    - Ensure the View receives the `student` model to access `student_code` and name.

## Tasks / Subtasks

- [x] **Controller Logic**
  - [x] Modify `DashboardController::index` in `app/Http/Controllers/DashboardController.php`.
  - [x] Add check: `Auth::user()->student->application->status === 'approved'`.
  - [x] If true, return `student.dashboard` view.
  - [x] Ensure `student` data is passed to the view.

- [x] **Student Dashboard View**
  - [x] Create `resources/views/student/dashboard.blade.php`.
  - [x] Implement layout using `x-app-layout` and `x-card`.
  - [x] Display Welcome message and Student ID.
  - [x] Create grid of cards for "My Grades", "Class Schedule", "Fee Statement".

- [x] **Tests**
  - [x] Create `tests/Feature/Student/StudentDashboardTest.php`.
  - [x] Test: Approved student sees student dashboard.
  - [x] Test: Unapproved student sees applicant dashboard.
  - [x] Test: Student ID is visible.

## Dev Notes

- **Relevant Architecture Patterns:**
  - **Single Entry Point:** Keep `/dashboard` as the single route. Logic inside `DashboardController` handles the branching.
  - **Component Usage:** Use `x-card` for the UI elements to match the existing applicant dashboard style.
  - **Strict Types:** Ensure `declare(strict_types=1);` is present in the Controller and Tests.

- **Source Tree Components:**
  - `app/Http/Controllers/DashboardController.php` (Modify)
  - `resources/views/student/dashboard.blade.php` (New)
  - `tests/Feature/Student/StudentDashboardTest.php` (New)

- **Testing Standards:**
  - Use **Pest** syntax (`it('shows student dashboard...', function() { ... })`).
  - Use Factories to generate the `approved` state: `Application::factory()->approved()->create()`.

### Project Structure Notes

- **Views:** Place all student-specific views in `resources/views/student/` to keep them separate from the applicant views.
- **Routes:** Do not add new routes for the dashboard itself, but ensure the links (`/student/grades`, etc.) are defined (even if placeholders for now, though Story 5.4 covers them - for this story, just the links are needed, maybe 404 or "Coming Soon" if not ready, but the story implies just the dashboard with links). *Correction:* Story 5.4 "View Grades & Schedule" implements the actual pages. This story just sets up the dashboard *links*.

### References

- **UX Design:** `_bmad-output/planning-artifacts/ux-design-specification.md` - "Journey 3: Post-Admission (Student View)".
- **Database:** `students` table has `student_code` column (Verified in `2024_01_01_000003_create_students_table.php`).

## Dev Agent Record

### Agent Model Used

opencode (Manual)

### Debug Log References

- Verified `student_code` column in migration.
- Confirmed Epics/Stories alignment.

### Completion Notes List

- Implemented `DashboardController` logic to conditionally render student view.
- Created `student/dashboard.blade.php` with `x-app-layout` and `x-ui.card`.
- Added Feature tests in `tests/Feature/Student/StudentDashboardTest.php` covering approved/unapproved states and content visibility.
- Verified tests pass.

### Senior Developer Review (AI)

- **Fixed Critical Logic Flaw**: Dashboard access now correctly prioritizes student status (via `student_code`) over the status of the *latest* application. Previously, starting a new draft application would lock an approved student out of their dashboard.
- **Fixed Hardcoded URLs**: Replaced raw URLs with named routes in `dashboard.blade.php` and added placeholder routes in `web.php`.
- **Improved Performance**: Added eager loading for `student` relationship in `DashboardController` to prevent N+1 queries.
- **Enhanced Testing**: Added test case for "Approved student with new draft application" scenario.

### File List

- `app/Http/Controllers/DashboardController.php`
- `resources/views/student/dashboard.blade.php`
- `tests/Feature/Student/StudentDashboardTest.php`
- `routes/web.php`
