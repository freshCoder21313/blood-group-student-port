# Story 5.1: Admin Payment Verification Panel

Status: done

<!-- Note: Validation is optional. Run validate-create-story for quality check before dev-story. -->

## Story

As a Finance Admin,
I want to view and approve manual payment receipts,
So that applications with manual payments can proceed to the "Submitted" state and be synced to ASP.

## Acceptance Criteria

1.  **Admin Route Created:** Create a route `/admin/payments` protected by `auth` middleware (and potentially a role check if roles exist, otherwise standard auth for now as per MVP).
2.  **List Pending Payments:** Display a list of all payments where `status` is `pending_verification`.
    *   Show Student Name, Application ID, Transaction Code, Amount.
3.  **View Payment Details:** Click to view full details including the "Proof Image" (screenshot).
4.  **Approve Action:**
    *   Update Payment status to `completed` (or `paid`).
    *   Ensure related Application is in `pending_approval` (it should be already, but if it was held, submit it).
    *   *Clarification:* `ApplicationService::submit` allows `pending_verification` payments to submit. The critical part is that ASP might reject it later if not paid, OR we want to verify before ASP sync. For now, just update payment status.
5.  **Reject Action:**
    *   Update Payment status to `failed`.
    *   Trigger an email notification to the user ("Payment Verification Failed").
    *   Revert Application status from `pending_approval` to `draft` (or `payment_failed`) so user can resubmit? *Decision:* Revert to `draft` so they can edit/retry payment.

## Tasks / Subtasks

- [x] **Route & Controller Setup**
  - [x] Create `Admin/PaymentController` in `app/Http/Controllers/Admin`.
  - [x] Add route `Route::get('/admin/payments', ...)` in `routes/web.php` (protected).
- [x] **Payment List View**
  - [x] Create `resources/views/admin/payments/index.blade.php`.
  - [x] Fetch payments with `pending_verification` status.
  - [x] Display table with columns: Date, Student, Code, Amount, Actions.
- [x] **Payment Detail & Action View**
  - [x] Create `resources/views/admin/payments/show.blade.php` (or modal).
  - [x] Display `proof_document_path` image (ensure secure access via `DocumentController` or temporary URL if private).
  - [x] Add "Approve" and "Reject" forms/buttons.
- [x] **Business Logic Implementation**
  - [x] Implement `approve` method in Controller (or Service).
    - Update Payment status.
    - Log action.
  - [x] Implement `reject` method.
    - Update Payment status.
    - Send Email (Notification).
    - Revert Application status if needed.
- [x] **Tests**
  - [x] Feature test: Admin can see pending payments.
  - [x] Feature test: Admin can approve payment.
  - [x] Feature test: Admin can reject payment.

## Dev Notes

- **Existing Architecture:**
  - `Payment` model exists.
  - `Application` model exists.
  - `MpesaService` handles payment logic, consider adding `verifyManualPayment($payment, $status)` method there or in a new `PaymentVerificationService`?
  - *Decision:* Keep it simple. Use `PaymentController` for now or add to `PaymentService` if logic is complex.
- **Security:**
  - Proof documents are in `private` storage. Use the existing mechanism to serve them (likely `DocumentController::show` or similar logic). Do NOT expose direct storage paths.
- **Notifications:**
  - Reuse existing Notification infrastructure (`App\Notifications`). Create `PaymentVerified` and `PaymentRejected` notifications.

### Project Structure Notes

- **Controllers:** Place in `app/Http/Controllers/Admin` to separate from Student/API logic.
- **Views:** `resources/views/admin/payments` (Admin namespace).
- **Routes:** Group under `admin` prefix in `web.php`.

### References

- Epic 5 in `_bmad-output/planning-artifacts/epics.md`.
- `MpesaService.php` for payment model interactions.

## Dev Agent Record

### Agent Model Used

opencode (Manual Generation)

### Debug Log References

- Checked `sprint-status.yaml`
- Checked `MpesaService.php`
- Checked `AspSyncController.php`

### Completion Notes List

- Confirmed `pending_verification` status usage.
- Confirmed `pending_approval` application status.
- Implemented `Admin/PaymentController` with `index`, `show`, `downloadProof`, `approve`, `reject`.
- Added `PaymentRejected` notification for users.
- Added feature tests covering all ACs.
- Ensured proof documents are served securely via `Storage::disk('private')->response()`.
- [Code Review Fix] Implemented `PaymentVerified` notification logic.
- [Code Review Fix] Updated `approve` method to set Application status to `pending_approval`.
- [Code Review Fix] Enabled strict test assertions for application status rollback.

### File List

- `app/Http/Controllers/Admin/PaymentController.php`
- `resources/views/admin/payments/index.blade.php`
- `resources/views/admin/payments/show.blade.php`
- `routes/web.php`
- `app/Notifications/PaymentRejected.php`
- `app/Notifications/PaymentVerified.php`
- `tests/Feature/Admin/PaymentVerificationTest.php`
