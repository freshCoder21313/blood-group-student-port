# Story 3.1: M-Pesa STK Push Integration (Web)

Status: done

<!-- Note: Validation is optional. Run validate-create-story for quality check before dev-story. -->

## Story

As a Applicant,
I want to initiate an M-Pesa payment from the portal,
So that I can pay the admission fee conveniently.

## Acceptance Criteria

1. **Given** I am on the Payment step
   **When** I enter my phone number and click "Pay"
   **Then** an STK Push is triggered via `MpesaService`
   **And** the UI shows a "Waiting for confirmation" spinner
   **And** I receive a prompt on my phone to enter my PIN
   **And** a record is created in `payments` table with status 'pending'

2. **Given** the STK Push is successful (User enters PIN)
   **When** M-Pesa sends the callback
   **Then** the system updates the `payments` record to 'completed'
   **And** the `applications` record (if linked) is updated to 'payment_received' (or similar status tracking)

3. **Given** the UI is waiting
   **When** the payment status changes to 'completed'
   **Then** the UI automatically updates (via polling) to show the Success Receipt
   **And** the "Submit Application" button becomes enabled

4. **Given** the payment fails (User cancels or wrong PIN)
   **When** the callback is received or timeout occurs
   **Then** the UI shows a friendly error message
   **And** allows me to retry

5. **Given** I am an Auditor
   **When** a payment is processed
   **Then** the system logs `MerchantRequestID`, `CheckoutRequestID`, and `Amount` for reconciliation

## Tasks / Subtasks

- [x] Database Schema
  - [x] Create `payments` table migration
    - `application_id` (FK, nullable if needed, but usually linked)
    - `transaction_code` (string, nullable initially)
    - `phone_number` (string)
    - `amount` (decimal)
    - `status` (enum: pending, completed, failed)
    - `merchant_request_id` (string, index)
    - `checkout_request_id` (string, index)
    - `mpesa_receipt_number` (string, nullable)
    - `result_desc` (text, nullable)
  - [x] Create `Payment` model with `application()` relationship

- [x] Configuration & Service Layer
  - [x] Create `config/mpesa.php` (Consumer Key, Secret, Passkey, Callbacks)
  - [x] Create `app/Services/Payment/MpesaService.php`
    - `initiateStkPush($phone, $amount, $reference)`
    - Handle Authentication (generate token)
    - `processCallback($payload)`
  - [x] Create `PaymentFailedException`

- [x] Backend Implementation
  - [x] Create `PaymentController`
    - `store(Request $request)`: Initiate payment
    - `callback(Request $request)`: Handle webhook (skip CSRF)
    - `checkStatus(Application $application)`: For frontend polling
  - [x] Update `ApplicationService` to check payment status before final submission

- [x] Frontend Implementation
  - [x] Create `x-mpesa-receipt` component (as per UX)
  - [x] Create `resources/views/application/payment.blade.php`
    - Phone Input (Validation: Safaricom formats)
    - "Pay Now" Button (Loading state)
    - Polling logic (Alpine.js) to check status every 3-5s
    - Success State (Show Receipt) / Error State (Retry)

- [x] Security & Testing
  - [x] Implement Signature Verification/IP Whitelisting for Callback
  - [x] Feature Test: `StudentCanPayViaMpesaTest`
    - Mock M-Pesa API responses
    - Test Callback processing logic
    - Test Polling endpoint
  - [x] Unit Test: `MpesaServiceTest` (Token generation, Payload construction)

## Dev Notes

- **Architecture Compliance:**
  - **Service:** `MpesaService` must be the ONLY entry point for Safaricom APIs.
  - **Controller:** `PaymentController` must delegate logic to the service.
  - **Security:** Callback route MUST be excluded from CSRF protection (`bootstrap/app.php`).
  - **Logging:** Use `Log::channel('daily')` or dedicated channel for financial transactions. Log every STK Push attempt and Callback payload.

- **UX/UI Requirements:**
  - **Optimistic UI:** Show "Processing" immediately.
  - **Polling:** Since WebSockets are Phase 2, implement robust polling using `setInterval` in Alpine.js. Stop polling on success or after X minutes.

- **Integration Details:**
  - **Callback URL:** Must be a publicly accessible URL. For local dev (Sail), use a tool like Ngrok or just Mock the callback in tests.
  - **Credentials:** Use `.env` variables (`MPESA_CONSUMER_KEY`, etc.). DO NOT hardcode.

### Project Structure Notes

- `app/Services/Payment/MpesaService.php`: New
- `app/Http/Controllers/PaymentController.php`: New
- `config/mpesa.php`: New
- `database/migrations/xxxx_create_payments_table.php`: New

### References

- [Source: _bmad-output/planning-artifacts/architecture.md#Integration Points] (M-Pesa)
- [Source: _bmad-output/planning-artifacts/ux-design-specification.md#Component Strategy] (MpesaReceipt)

## Dev Agent Record

### Agent Model Used

{{agent_model_name_version}}

### Debug Log References

### Completion Notes List

- Implemented M-Pesa STK Push flow including PaymentController, MpesaService, and Payment model updates.
- Added `x-mpesa-receipt` component and `application/payment` view with Alpine.js polling.
- Implemented IP Whitelisting middleware for M-Pesa callback.
- Updated ApplicationService to require payment completion before submission.
- Added comprehensive tests for all layers.
- Created ProgramFactory to fix existing test failures.
- [AI-Review] Excluded M-Pesa callback from CSRF protection to prevent 419 errors.
- [AI-Review] Fixed MpesaService to update Application status to 'payment_received'.
- [AI-Review] Removed broken and unused Api/V1/PaymentController to prevent routing conflicts.
- [AI-Review] Refactored PaymentController to use config-based amount.

### File List

- database/migrations/2026_01_21_081513_align_payments_table_with_story_3_1.php
- app/Models/Payment.php
- config/mpesa.php
- app/Services/Payment/MpesaService.php
- app/Exceptions/PaymentFailedException.php
- database/factories/PaymentFactory.php
- database/factories/ProgramFactory.php
- app/Http/Controllers/PaymentController.php
- routes/web.php
- routes/api.php
- app/Services/Application/ApplicationService.php
- config/admission.php
- resources/views/components/mpesa-receipt.blade.php
- resources/views/application/payment.blade.php
- app/Http/Controllers/ApplicationFormController.php
- app/Http/Middleware/VerifyMpesaIp.php
- tests/Feature/Mpesa/PaymentsTableTest.php
- tests/Unit/PaymentTest.php
- tests/Unit/Config/MpesaConfigTest.php
- tests/Unit/Services/Payment/MpesaServiceTest.php
- tests/Unit/Exceptions/PaymentFailedExceptionTest.php
- tests/Feature/Payment/PaymentFlowTest.php
- tests/Unit/Services/ApplicationServicePaymentCheckTest.php
- tests/Feature/View/PaymentViewTest.php
- tests/Feature/Security/MpesaCallbackSecurityTest.php
- tests/Unit/Services/ApplicationServiceTest.php
