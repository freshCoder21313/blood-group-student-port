**🔥 CODE REVIEW FINDINGS, Wavister!**

**Story:** 3-2-manual-payment-fallback
**Git vs Story Discrepancies:** 16 found
**Issues Found:** 1 High, 3 Medium, 2 Low

## 🔴 CRITICAL ISSUES
- **Hardcoded Payment Amount:** `PaymentController.php` uses `config('admission.payment.amount', 1000)` inside the `storeManual` method. While config-based is better than raw integer, burying this in the controller is brittle. It should ideally come from the `Application` or `Program` model logic (e.g., `$application->program->fee`), or a dedicated Service method `calculateFee($application)`.

## 🟡 MEDIUM ISSUES
- **Git/Story State Mismatch:** The git working directory contains **uncommitted changes** from Story 3.1 (STK Push), including `VerifyMpesaIp.php` and `PaymentFailedException.php`. These are NOT listed in Story 3.2's File List. This makes isolation testing of 3.2 difficult and risky.
- **Missing Client-Side Validation:** `payment.blade.php` Manual Payment form input lacks the `pattern` attribute. Users submitting invalid codes (e.g., lowercase, short) must wait for a server roundtrip to see an error.
  - *Fix:* Add `pattern="[A-Z0-9]{10}"` and `title="10-character uppercase alphanumeric code"` to the input.
- **Incomplete Test Coverage:** `ManualPaymentSubmissionTest.php` asserts the payment is created but does **not** verify the `manual_submission` boolean flag is set to `true`. This is a critical logic piece for reporting/analytics.

## 🟢 LOW ISSUES
- **UX Inconsistency (Step Number):** `payment.blade.php` header says "Step 5: Payment". The UX Design Specification and Progress Bar indicate this is Step 4 (Personal -> Parent -> Program -> Documents -> Payment? Wait, UX says 4 cards. Personal, Parent, Docs, Payment. Program is likely inside/merged or Step 3. If Program is Step 3, Documents is Step 4, Payment is Step 5? Need to check WizardSteps. Regardless, consistency check needed).
- **Unused Flash Message:** `PaymentController::storeManual` redirects with `with('status', 'payment-verification-pending')`. The Blade view logic seems to rely on `$payment->status` from the database (via x-data init) rather than `session('status')`.
