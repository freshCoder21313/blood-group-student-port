# Story 1.2: PII Encryption Schema Migration

Status: done

<!-- Note: Validation is optional. Run validate-create-story for quality check before dev-story. -->

## Story

As a Compliance Officer,
I want `national_id` and `passport_number` columns to support encryption and blind indexing,
So that student data is secure at rest.

## Acceptance Criteria

1. **Given** the existing `students` table
2. **When** I run the new migration
3. **Then** `national_id` and `passport_number` columns are modified to `TEXT` (to support long encrypted strings)
4. **And** new columns `national_id_index` and `passport_number_index` are added for searching
5. **And** the database schema supports encryption at rest

## Tasks / Subtasks

- [x] **Create Migration**
  - [x] Run `php artisan make:migration modify_students_table_for_pii_encryption --table=students`
  - [x] Implement `up()`: Change `national_id` and `passport_number` to `text` and `nullable` (if needed). Add `national_id_index` and `passport_number_index` (string, index).
  - [x] Implement `down()`: Revert columns to original state (likely `string`) and drop index columns.
- [x] **Update Student Model**
  - [x] Add `protected function casts(): array` with `'national_id' => 'encrypted'` and `'passport_number' => 'encrypted'`.
  - [x] Add "Blind Indexing" logic: Use a model observer or `booted()` method to automatically hash `national_id` to `national_id_index` on save.
- [x] **Testing (Pest)**
  - [x] Create `tests/Feature/StudentPiiTest.php`.
  - [x] Test: `it_encrypts_pii_in_database` (Assert DB has different/longer value than input).
  - [x] Test: `it_decrypts_pii_on_retrieval` (Assert model attribute matches input).
  - [x] Test: `it_populates_blind_indexes` (Assert `_index` column is populated).
  - [x] Test: `it_can_find_student_by_blind_index` (Simulate exact match lookup).

## Dev Notes

### Architecture Compliance
- **Encryption:** Use Laravel 11's native [Encrypted Casting](https://laravel.com/docs/11.x/eloquent-mutators#encrypted-casting).
- **Blind Indexing:** To allow searching (e.g., "Does this ID exist?"), store a hashed version of the PII in `*_index` columns. Use a consistent hashing algorithm (e.g., `hash_hmac('sha256', $value, config('app.key'))`).
- **Schema:** Encrypted values are significantly longer than plain text. Use `TEXT` data type for the encrypted columns.

### Project Structure Notes
- **Migrations:** Do not modify the existing `2024_01_01_000003_create_students_table.php`. Create a **new** migration file.
- **Models:** logic should be in `app/Models/Student.php`.

### References
- [Source: _bmad-output/planning-artifacts/epics.md#Story 1.2]
- [Source: _bmad-output/planning-artifacts/architecture.md#Authentication & Security]

## Senior Developer Review (AI)

**Review Date:** Wed Jan 21 2026
**Outcome:** Approved

### Action Items
- [x] [HIGH] Implement Search Scopes: Added `scopeWhereNationalId` and `scopeWherePassportNumber` to abstract hashing logic.
- [x] [MEDIUM] Migration Safety: Updated `down()` to use `text` type to prevent data corruption during rollback.
- [x] [LOW] Test Hygiene: Added test environment files to `.gitignore`.
- [x] [HIGH] Git Hygiene: Removed `.env.testing` and `create_test_db.php` from git tracking (un-staged) to prevent secret leakage.
- [x] [MEDIUM] Architecture: Introduced `BLIND_INDEX_KEY` config to decouple blind indexing from `APP_KEY`, ensuring index stability if app key rotates.
- [x] [MEDIUM] Documentation: Updated File List to include all modified files (`phpunit.xml`, `config/app.php`, etc.).

## Dev Agent Record

### Agent Model Used

OpenCode

### Debug Log References

- Encountered issue with `sqlite` missing in test environment. Switched to `mysql` using a custom test database.
- Encountered missing `StudentFactory`, created it.
- Encountered pre-existing failures in `ProfileTest`, attempted to patch but discovered deeper misalignment between Controller validation and User model (missing 'name' column).
- Verified story functionality with `StudentPiiTest` (5/5 tests passed).

### Completion Notes List

- Implemented PII encryption for `national_id` and `passport_number`.
- Added blind indexing using HMAC SHA-256 in `Student` model observer.
- Created `StudentFactory` to support testing.
- Created comprehensive test suite `StudentPiiTest` covering:
    - Encryption in DB.
    - Decryption on retrieval.
    - Blind index population.
    - Blind index clearing (edge case).
    - Searching by blind index.
- **Validation**: All acceptance criteria met.
- **Code Review Fixes**:
    - Implemented `scopeWhereNationalId` and `scopeWherePassportNumber` in `Student` model.
    - Refactored hashing logic into `generateBlindIndex`.
    - Updated migration rollback to prevent data loss.
    - Updated `.gitignore` for test artifacts.
    - **Adversarial Review Fixes**:
        - Un-staged `.env.testing` and `create_test_db.php` to prevent committing secrets.
        - Added `BLIND_INDEX_KEY` to `config/app.php` and `Student` model.
        - Updated `phpunit.xml` to include test `BLIND_INDEX_KEY`.

- **Known Issues**: Existing tests (`ProfileTest`, `PasswordResetTest`) have failures unrelated to this story. These appear to be legacy technical debt (e.g., mismatch between `users` table structure and Breeze-generated tests).

### File List

- student-admission-portal/database/migrations/2026_01_21_003712_modify_students_table_for_pii_encryption.php
- student-admission-portal/app/Models/Student.php
- student-admission-portal/tests/Feature/StudentPiiTest.php
- student-admission-portal/database/factories/StudentFactory.php
- student-admission-portal/tests/Feature/ProfileTest.php
- student-admission-portal/config/app.php
- student-admission-portal/.env.example
- student-admission-portal/.gitignore
- student-admission-portal/phpunit.xml
