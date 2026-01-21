# Story 2.4: Document Upload Component & Storage

Status: done

<!-- Note: Validation is optional. Run validate-create-story for quality check before dev-story. -->

## Story

As a Applicant,
I want to upload my transcripts and ID documents,
so that I can provide proof of my qualifications.

## Acceptance Criteria

1. **Given** I am on the "Documents" step of the application wizard
   **When** I select a file (PDF or Image, max 5MB) for "National ID" or "Transcript"
   **Then** the file is uploaded
   **And** stored securely in the `private` disk (not `public` folder)
   **And** a record is created in the `documents` table linked to my application

2. **Given** I have uploaded a document
   **When** I view the form
   **Then** I see a preview (thumbnail for images, icon for PDF)
   **And** the file name is visible
   **And** I see a "Remove" button to delete the file

3. **Given** I try to upload an invalid file (e.g., .exe, > 5MB)
   **Then** the system rejects it with a user-friendly error message
   **And** the file is not stored

4. **Given** I want to view my uploaded document
   **When** I click the preview link
   **Then** the system serves the file via a secure route (`/documents/{id}`)
   **And** verifies I am the owner of the application (via Policy) before serving

5. **Given** I am saving as Draft
   **When** I have not uploaded all required documents
   **Then** I can still save the step (it is not marked complete)

6. **Given** I want to proceed to the next step
   **When** I click "Next"
   **Then** the system validates that all required documents (ID, Transcript) are present
   **And** marks the step as complete

## Tasks / Subtasks

- [x] Database Schema
  - [x] Create `documents` table migration
    - `application_id` (FK)
    - `type` (enum: 'national_id', 'transcript', etc.)
    - `path` (string)
    - `original_name` (string)
    - `mime_type` (string)
    - `size` (integer)
  - [x] Create `Document` model with relationships (`application`)

- [x] Backend Implementation
  - [x] Configure `filesystems.php` for `private` disk (local driver, `storage/app/private`)
  - [x] Create `DocumentService`
    - `store($application, $file, $type)`
    - `delete($document)`
    - `getUrl($document)` (temporary URL or route to controller)
  - [x] Create `DocumentController`
    - `download(Document $document)`: Handles secure file serving
  - [x] Update `ApplicationService`
    - Add `getDocuments($application)` helper
    - Update `saveStep` logic for documents step to check for required docs

- [x] Frontend Implementation
  - [x] Create `x-image-uploader` component
    - Drag & drop zone / File picker
    - Preview state
    - Error state
    - Progress indicator (optional/optimistic)
  - [x] Create `resources/views/application/documents.blade.php` view
    - Implement uploaders for "National ID" and "High School Transcript"

- [x] Security & Testing
  - [x] Create `DocumentPolicy` (view/delete own docs only)
  - [x] Feature Test: `StudentCanUploadDocumentsTest`
    - Test upload success (private storage)
    - Test validation (size/type)
    - Test unauthorized access to download route
    - Test draft saving vs completion

## Dev Notes

- **Architecture Compliance:**
  - **Storage:** MUST use `Storage::disk('private')`. Do NOT store in `public/`.
  - **Access:** Create a dedicated route `GET /documents/{document}` that uses `DocumentController@show`. This controller MUST authorize the action using `DocumentPolicy` before streaming the file response (`return Storage::disk('private')->download(...)`).
  - **Service Layer:** All file handling logic (naming, storage, DB creation) goes in `DocumentService`.

- **UX/UI Requirements:**
  - **Recommendation:** Use Alpine.js for the `x-image-uploader` to handle file selection preview immediately (Client-side preview) before upload.

### Project Structure Notes

- `app/Services/DocumentService.php`: New service.
- `app/Models/Document.php`: New model.
- `routes/web.php`: Add `get('/documents/{document}', ...)` route.

### References

- [Source: _bmad-output/planning-artifacts/architecture.md#Infrastructure & Deployment] (Private Volume)
- [Source: _bmad-output/planning-artifacts/ux-design-specification.md#Component Strategy] (ImageUploader)
- [Source: _bmad-output/implementation-artifacts/2-3-program-selection-logic.md] (Previous story pattern)

## Senior Developer Review (AI)

_Reviewer: Wavister on Wed Jan 21 2026_

### Findings
- **High Severity:** Broken image preview for saved files. The frontend component expected a file extension in the URL to determine if it was an image, but the secure route (`/documents/{id}`) does not provide one. Fixed by passing `initial-is-image` prop from the server based on mime type.
- **Medium Severity:**
  - Missing `strict_types=1` in `Document` model. Fixed.
  - `documents` table allowed nullable metadata fields. Fixed to be required for data integrity.

### Outcome
- **Status:** Approved (Auto-fixed)
- **Action Items:** None (All issues resolved)

## Dev Agent Record

### Agent Model Used

Opencode (Simulating BMad Workflow)

### Debug Log References

- Encountered configuration issue with Pest and `uses(Tests\TestCase::class)`, resolved by relying on global configuration.
- Fixed file structure issue in `ApplicationService.php` where methods were inserted incorrectly.

### Completion Notes List

- Implemented `documents` table with fields `path`, `mime_type`, `size`.
- Configured `private` filesystem disk.
- Created `DocumentService` for storage handling.
- Implemented secure `DocumentController` download with `DocumentPolicy`.
- Created Alpine.js based `x-image-uploader` component with drag-and-drop and preview.
- Created `application.documents` view and updated controller logic.
- Implemented comprehensive tests:
  - `DocumentTest` (Unit)
  - `FilesystemTest` (Unit)
  - `DocumentServiceTest` (Unit)
  - `ApplicationServiceTest` (Unit)
  - `DocumentDownloadTest` (Feature - Security)
  - `StudentCanUploadDocumentsTest` (Feature - Story Flow)
- **Review Fixes**:
  - Implemented `DocumentService` duplicates logic (delete old before create new).
  - Implemented `DocumentController::destroy` and `DELETE` route.
  - Updated `x-image-uploader` to handle file deletion via `fetch`.
  - Added `DocumentDeleteTest.php`.

### File List

- student-admission-portal/app/Models/Document.php
- student-admission-portal/database/migrations/2024_01_01_000008_create_documents_table.php
- student-admission-portal/config/filesystems.php
- student-admission-portal/app/Services/DocumentService.php
- student-admission-portal/app/Http/Controllers/DocumentController.php
- student-admission-portal/routes/web.php
- student-admission-portal/app/Policies/DocumentPolicy.php
- student-admission-portal/resources/views/components/image-uploader.blade.php
- student-admission-portal/resources/views/application/documents.blade.php
- student-admission-portal/app/Http/Controllers/ApplicationFormController.php
- student-admission-portal/app/Services/Application/ApplicationService.php
- student-admission-portal/tests/Unit/DocumentTest.php
- student-admission-portal/tests/Unit/FilesystemTest.php
- student-admission-portal/tests/Unit/DocumentServiceTest.php
- student-admission-portal/tests/Unit/ApplicationServiceTest.php
- student-admission-portal/tests/Feature/DocumentDownloadTest.php
- student-admission-portal/tests/Feature/StudentCanUploadDocumentsTest.php
- student-admission-portal/tests/Feature/DocumentDeleteTest.php
