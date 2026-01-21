# Story 3.4: Payment Callback Handling

Status: done

<!-- Note: Validation is optional. Run validate-create-story for quality check before dev-story. -->

## Story

As a System,
I want to process M-Pesa callbacks automatically,
so that payments are verified in real-time.

## Acceptance Criteria

1. **Given** M-Pesa sends a callback to the callback URL
   **When** the system receives the payload
   **Then** `PaymentController` validates the signature/origin (via IP Whitelist or Secret)
   **And** updates the corresponding `payments` record status to `completed` or `failed`
   **And** updates the `transaction_code` and `mpesa_receipt_number` from metadata
   **And** DOES NOT automatically submit the application (Status remains `draft` until user clicks Submit, per Story 3.3)

2. **Given** a duplicate callback is received (same CheckoutRequestID)
   **When** processed
   **Then** the system handles it idempotently (does not duplicate records or error out)

3. **Given** an invalid callback (wrong signature/structure)
   **When** received
   **Then** the system rejects it with 401 Unauthorized or 400 Bad Request
   **And** logs the security attempt

## Tasks / Subtasks

- [x] **Security Enhancement**
  - [x] Verify `VerifyMpesaIp` middleware is active and working
  - [x] (Optional) Implement M-Pesa Signature Validation if IP whitelisting is insufficient (Research "M-Pesa Signature Header")

- [x] **Backend Refactoring (Fixes)**
  - [x] Update `MpesaService::processCallback`:
    - [x] REMOVE the logic that auto-updates Application status to `pending_approval`
    - [x] Ensure it ONLY updates the `Payment` model (Status, Receipt Number, Amount)
    - [x] Ensure `transaction_code` is correctly mapped from `MpesaReceiptNumber`

- [x] **Testing**
  - [x] Create `PaymentCallbackTest`
    - [x] Test valid success callback -> Payment becomes `completed`, Application remains `draft`
    - [x] Test failed callback -> Payment becomes `failed`
    - [x] Test duplicate callback -> No side effects
    - [x] Test invalid signature/IP -> 401/403

## Dev Notes

- **Critical Fix:** The current `MpesaService` implementation (from Story 3.1) incorrectly updates `Application` status to `pending_approval`. This contradicts Story 3.3 which requires a manual "Submit" action after validation. You MUST remove this side effect.
- **Idempotency:** The existing `update` logic is likely idempotent, but verify it doesn't overwrite `manual_submission` data if a callback arrives late.
- **Validation:** M-Pesa callbacks do not always have a signature header in the Sandbox. IP Whitelisting (`VerifyMpesaIp`) is the primary defense. Ensure it's applied to the route.

### Project Structure Notes

- `app/Http/Middleware/VerifyMpesaIp.php`: Check existence
- `app/Services/Payment/MpesaService.php`: Modify `processCallback`
- `tests/Feature/PaymentCallbackTest.php`: Create

### References

- [Source: _bmad-output/implementation-artifacts/3-3-final-submission-logic.md] (Defines explicit Submission flow)
- [Source: app/Services/Payment/MpesaService.php] (Current implementation to refactor)
- [Source: routes/api.php] (Route definition)

## Dev Agent Record

### Agent Model Used

opencode-1

### Debug Log References

- Verified `VerifyMpesaIp` logic with tests.
- Removed Application status update from `MpesaService` to comply with Story 3.3.
- Implemented `PaymentCallbackTest` covering IP blocks, success/fail scenarios, and idempotency.

### Completion Notes List

- Refactored `MpesaService::processCallback` to decouple payment completion from application submission.
- Added comprehensive `PaymentCallbackTest` feature tests.
- Verified Security Enhancement (IP Whitelisting) is active.
- Opted to rely on IP Whitelisting as primary defense over optional Signature Validation for Sandbox compatibility.

#### Senior Developer Review Fixes (AI)
- **Security:** Redacted PII (Phone Numbers) from `PaymentController` logs.
- **Architecture:** Moved hardcoded IP whitelist from Middleware to `config/mpesa.php`.
- **Robustness:** Added validation for missing `MpesaReceiptNumber` in success callbacks.
- **Workflow:** Ensure `manual_submission` flag is cleared when STK callback confirms payment.
- **Tests:** Added coverage for new robustness checks.

### File List

- student-admission-portal/app/Services/Payment/MpesaService.php
- student-admission-portal/tests/Feature/PaymentCallbackTest.php
- student-admission-portal/app/Http/Controllers/PaymentController.php
- student-admission-portal/app/Http/Middleware/VerifyMpesaIp.php
- student-admission-portal/config/mpesa.php
