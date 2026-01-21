# Story 1.3: Web-Based OTP Integration

Status: done

<!-- Note: Validation is optional. Run validate-create-story for quality check before dev-story. -->

## Story

As a Student,
I want to verify my phone/email via OTP during the web registration process,
so that my identity is confirmed.

## Acceptance Criteria

1. **Registration Redirection**:
   - **Given** a user registers on the web
   - **When** they submit the registration form
   - **Then** they are redirected to an "Enter OTP" screen
   - **And** they are NOT logged in yet.

2. **OTP Generation & Storage**:
   - **Given** a valid registration
   - **Then** a 6-digit numeric OTP is generated
   - **And** it is stored in the `otps` table with `purpose='registration'`
   - **And** it is sent via the registered channel (Email/SMS).

3. **Verification Logic**:
   - **Given** the user is on the OTP screen
   - **When** they enter the correct code
   - **Then** the `otps` record is marked as `verified_at`
   - **And** the user's `email_verified_at` (or `phone_verified_at`) is updated
   - **And** the user is logged in and redirected to the Dashboard.

4. **Security & Limits**:
   - **Given** an incorrect OTP is entered
   - **Then** the `attempts` counter in `otps` table is incremented
   - **And** after 3 failed attempts, the OTP is invalidated.
   - **And** users cannot access the Dashboard without verification.

5. **Login Interception**:
   - **Given** a user who registered but hasn't verified OTP logs in
   - **Then** they are redirected back to the OTP verification screen.

## Tasks / Subtasks

- [x] **Backend: OTP Service Implementation**
  - [x] Create `app/Services/Auth/OtpService.php`.
  - [x] Implement `generate(User $user, string $purpose)`: Create record in `otps`.
  - [x] Implement `verify(string $identifier, string $code, string $purpose)`: Validate and update `verified_at`.
  - [x] Add rate limiting/throttling logic (max 3 attempts).

- [x] **Backend: Registration Flow Update**
  - [x] Modify `RegisteredUserController::store` to prevent auto-login.
  - [x] Add `phone` field validation to Registration (update `rules`).
  - [x] Integrate `OtpService::generate` after user creation.
  - [x] Redirect to `otp.verify` route instead of dashboard.

- [x] **Backend: Login Interception**
  - [x] Create Middleware `EnsureOtpVerified` (or modify `AuthenticatedSessionController`).
  - [x] Check `email_verified_at` or `phone_verified_at` on login attempt.

- [x] **Backend: OTP Controller**
  - [x] Create `OtpVerificationController`.
  - [x] Implement `create()`: Return the view.
  - [x] Implement `store()`: Call service to verify, then `Auth::login`.
  - [x] Implement `resend()`: Generate new OTP.

- [x] **Frontend: OTP Views**
  - [x] Create `resources/views/auth/verify-otp.blade.php`.
  - [x] Use Blade Components (`x-guest-layout`, `x-primary-button`, `x-text-input`).
  - [x] Display validation errors (e.g., "Invalid code").

- [x] **Testing**
  - [x] Create `tests/Feature/Auth/OtpVerificationTest.php`.
  - [x] Test: Successful registration redirects to OTP.
  - [x] Test: Correct OTP verifies and logs in.
  - [x] Test: Incorrect OTP increments attempts/fails.
  - [x] Test: Unverified user login redirects to OTP.

## Dev Notes

- **Architecture Compliance**:
  - Use **Service Class** (`OtpService`) for all OTP logic. Do not put logic in Controllers.
  - Use **Blade Components** for the UI.
  - Use **Native Casting**/Carbon for timestamp checks (`expires_at`).

- **Database**:
  - `otps` table already exists (Migration `2024_01_01_000002`). Use it.
  - Columns: `identifier`, `otp_code`, `type` (email/sms), `purpose`, `expires_at`.

- **Security**:
  - OTPs should expire after 10-15 minutes.
  - Rate limit the `resend` endpoint (throttle:6,1).

### Project Structure Notes

- **Existing Auth**: Located in `app/Http/Controllers/Auth`. Keep the new `OtpVerificationController` there.
- **Routes**: Add new routes to `routes/auth.php` inside the `guest` middleware group.

### References

- [Source: database/migrations/2024_01_01_000002_create_otps_table.php] - Schema definition.
- [Source: app/Http/Controllers/Auth/RegisteredUserController.php] - Current registration logic.

## Dev Agent Record

### Agent Model Used

opencode-claude-3-5-sonnet

### Debug Log References

- Validated `otps` table schema: Confirmed presence of `identifier`, `otp_code`, `attempts`.
- Checked `users` table: Has `phone` column.

### Completion Notes List

- This story bridges the gap between the default Breeze email verification (link-based) and the required OTP-based flow.
- Ensure `AppServiceProvider` or Event Listeners do not interfere (e.g. `SendEmailVerificationNotification`). You might need to disable the default listener in `EventServiceProvider` if it exists, or just don't fire `Registered` event if you don't want the link sent. (Better: Keep `Registered` event but customize the listener to send OTP instead, or just send OTP directly from Controller/Service).
- Implemented `OtpService` to handle OTP generation and verification with Rate Limiting.
- Created `EnsureOtpVerified` middleware to intercept access to dashboard for unverified users.
- Updated `RegisteredUserController` to support `phone` and skip auto-login, redirecting to OTP verification.
- Added comprehensive Feature tests for OTP Flow, Registration, and Login Interception.
- Moved `otp.verify` routes out of `guest` middleware to support both guest (registration) and auth (login interception) flows.

### File List
- app/Services/Auth/OtpService.php
- app/Http/Controllers/Auth/OtpVerificationController.php
- app/Http/Controllers/Auth/RegisteredUserController.php
- app/Http/Middleware/EnsureOtpVerified.php
- resources/views/auth/verify-otp.blade.php
- routes/auth.php
- routes/web.php
- tests/Feature/Auth/OtpServiceTest.php
- tests/Feature/Auth/OtpVerificationTest.php
- tests/Feature/Auth/OtpLoginInterceptionTest.php
- tests/Feature/Auth/RegistrationTest.php
- bootstrap/app.php
- app/Http/Requests/ProfileUpdateRequest.php

## Senior Developer Review (AI)

**Reviewer:** Wavister (AI Agent)
**Date:** 2026-01-21
**Outcome:** Approved (Auto-Fixed)

### Findings & Fixes
- **CRITICAL**: Fixed OTP Service ignoring user's phone number. Logic updated to attempt SMS delivery if phone number exists.
- **MEDIUM**: Fixed potential crash (500 Error) in `OtpVerificationController` by wrapping `resend` logic in try/catch to handle Rate Limit exceptions gracefully.
- **MEDIUM**: Added missing `throttle:6,1` middleware to `otp.resend` route as required by specifications.
- **MEDIUM**: Updated File List to include `bootstrap/app.php` and `ProfileUpdateRequest.php` to match git reality.
