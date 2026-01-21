# Story 3.2: Manual Payment Fallback

Status: review

<!-- Note: Validation is optional. Run validate-create-story for quality check before dev-story. -->

## Story

As a Applicant,
I want to manually enter a Transaction ID if the automated payment fails,
So that I can still complete my application.

## Acceptance Criteria

1. **Given** I am on the Payment step
   **When** STK push fails OR I explicitly choose "Manual Payment" / "Paybill" option
   **Then** the UI shows the Paybill Number (e.g., 888888) and Account Number instructions
   **And** a form appears to enter the "M-Pesa Transaction Code" and "Upload Payment Proof"

2. **Given** I have paid via M-Pesa manually
   **When** I enter the Transaction Code (e.g., QDH...)
   **And** the code is less than 10 characters or contains special characters
   **Then** the system shows a validation error ("Invalid Transaction Code format")

3. **Given** I have entered a valid code
   **When** I upload a screenshot of the payment message
   **Then** the system validates the file (Image/PDF, max 5MB)
   **And** uploads it to the secure private storage

4. **Given** the form is valid
   **When** I click "Confirm Payment"
   **Then** a `Payment` record is created/updated with:
     - `status`: 'pending_verification'
     - `transaction_code`: The entered code
     - `proof_document_path`: The path to the uploaded file
   **And** the UI updates to show "Payment Under Verification"

## Tasks / Subtasks

- [x] **Database Schema Updates**
  - [x] Create migration `update_payments_table_for_manual_fallback`
    - Make `transaction_code` string (ensure it's not nullable if strictly required for completed/manual, or handle logic) - *Correction: It was nullable for STK push init, now required for manual.*
    - Add `proof_document_path` (string, nullable)
    - Update `status` enum to include `pending_verification` (if not using string)

- [x] **Backend Implementation**
  - [x] Update `Payment` model
    - Add `pending_verification` to status constants/enums
    - Add `proof_document_path` to fillable
  - [x] Update `MpesaService` (or create `ManualPaymentService`)
    - Add method `recordManualPayment(Application $app, array $data)`
    - Handle file storage logic (using `Storage::disk('private')`)
  - [x] Update `PaymentController`
    - Add `storeManual(Request $request)` endpoint
    - Validation: `transaction_code` (regex `/^[A-Z0-9]{10}$/`), `proof` (file, mimes:jpg,png,pdf, max:5120)

- [x] **Frontend Implementation**
  - [x] Update `resources/views/application/payment.blade.php`
    - Add "Problems paying? Use Paybill" toggle/link
    - Create "Manual Payment" section (hidden by default or toggled)
    - Show Paybill Instructions (Configurable via `config('mpesa.paybill')`?)
    - Add Input for Transaction Code (`x-input`)
    - Add Upload for Proof (Reuse `x-image-uploader`)
  - [x] Update `x-mpesa-receipt` to handle `pending_verification` state (Yellow badge?)

- [x] **Testing**
  - [x] Feature Test: `ManualPaymentSubmissionTest`
    - Test toggle visibility
    - Test validation errors
    - Test successful submission (DB record created, File stored)
  - [x] Unit Test: `MpesaService::recordManualPayment`

## Dev Notes

- **Working Directory:** All commands must be run in `student-admission-portal/`.
- **Private Storage:** Ensure the proof document is stored in the `private` disk (setup in Architecture) so it's not publicly accessible.
- **Validation:** strict regex for M-Pesa codes helps reduce typos.
- **UX:** The user might try STK push first, fail, then try Manual. The system should handle multiple Payment records or update the existing "pending" one?
  - *Decision:* If an existing "pending" payment exists for this application, UPDATE it or mark it failed and create new?
  - *Simplest:* Update the existing `Payment` record associated with the Application if it's in `pending` state. If no record, create one.

### Project Structure Notes

- `app/Services/Payment/MpesaService.php`: Extend
- `app/Http/Controllers/PaymentController.php`: Extend
- `database/migrations/xxxx_update_payments_table.php`: New
- `resources/views/application/payment.blade.php`: Modify

### References

- [Source: _bmad-output/planning-artifacts/ux-design-specification.md#Component Strategy] (x-image-uploader)
- [Source: _bmad-output/planning-artifacts/architecture.md#Data Architecture] (Private Volume)

## Dev Agent Record

### Agent Model Used

{{agent_model_name_version}}

### Debug Log References

- Fixed regression in `MpesaService` regarding `application->status` update (used `pending_approval` instead of invalid `payment_received`).
- Fixed missing GD extension issue by using `create` instead of `image` in tests.
- Fixed `MpesaService` undefined method by adding it.
- Fixed missing import in `MpesaService`.
- Fixed Route undefined.

### Completion Notes List

- Implemented migration for manual fallback fields and updated payment status enum.
- Implemented manual payment service logic in `MpesaService`.
- Implemented Controller endpoint `storeManual` and routes.
- Implemented Frontend UI with toggle and Pending Verification state in `payment.blade.php` and `x-mpesa-receipt`.
- Verified with Unit and Feature tests.
- Fixed existing regression in PaymentFlowTest.

### File List

- student-admission-portal/database/migrations/2026_01_21_092733_update_payments_table_for_manual_fallback.php
- student-admission-portal/app/Services/Payment/MpesaService.php
- student-admission-portal/tests/Feature/ManualPaymentSchemaTest.php
- student-admission-portal/app/Models/Payment.php
- student-admission-portal/tests/Unit/ManualPaymentModelTest.php
- student-admission-portal/tests/Unit/Services/Payment/MpesaManualPaymentTest.php
- student-admission-portal/tests/Feature/ManualPaymentSubmissionTest.php
- student-admission-portal/app/Http/Controllers/PaymentController.php
- student-admission-portal/routes/web.php
- student-admission-portal/tests/Feature/View/PaymentViewTest.php
- student-admission-portal/resources/views/application/payment.blade.php
- student-admission-portal/config/mpesa.php
- student-admission-portal/resources/views/components/mpesa-receipt.blade.php
