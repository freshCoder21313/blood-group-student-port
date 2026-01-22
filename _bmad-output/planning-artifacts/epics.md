---
stepsCompleted:
  - step-01-validate-prerequisites
  - step-02-design-epics
  - step-03-create-stories
  - step-04-final-validation
inputDocuments:
  - _bmad-output/planning-artifacts/prd.md
  - _bmad-output/planning-artifacts/architecture.md
  - _bmad-output/planning-artifacts/ux-design-specification.md
---

# blood-group-student-port - Epic Breakdown

## Overview

This document provides the complete epic and story breakdown for blood-group-student-port, decomposing the requirements from the PRD, UX Design if it exists, and Architecture requirements into implementable stories.

## Requirements Inventory

### Functional Requirements

*   **FR1:** User can register using an email address or phone number.
*   **FR2:** User can verify their identity via a One-Time Password (OTP).
*   **FR3:** User can log in and log out of the portal securely.
*   **FR4:** System must block access to protected routes for unauthenticated users.
*   **FR5:** User can initiate a new admission application.
*   **FR6:** User can save application progress as a 'Draft'.
*   **FR7:** User can input Personal Information (Name, DOB, ID).
*   **FR8:** User can input Parent/Guardian Information.
*   **FR9:** User can select a Program/Course from a predefined list.
*   **FR10:** User can submit the application only after all steps and payment are complete.
*   **FR11:** User can upload digital documents (Images, PDFs) for required artifacts (ID, Transcript).
*   **FR12:** User can view their uploaded documents to confirm correctness.
*   **FR13:** System must validate file types and size limits before acceptance.
*   **FR14:** User can initiate an M-Pesa payment request (STK Push).
*   **FR15:** User can manually enter a Transaction ID if STK Push fails (optional fallback).
*   **FR16:** System must receive and process M-Pesa payment callbacks to update application status.
*   **FR17:** External System (ASP) can query the Portal for 'Pending' applications via API.
*   **FR18:** External System (ASP) can update the status of an application (Approved/Rejected/Request Info) via API.
*   **FR19:** System must log all API requests for audit purposes.
*   **FR20:** System must send an email confirmation upon successful registration.
*   **FR21:** System must send an email notification when Application Status changes.
*   **FR22:** Finance Admin can verify manual payments.
*   **FR23:** Approved students can access a dedicated dashboard with student services.
*   **FR24:** Students can view their grades and class schedule.

### NonFunctional Requirements

*   **Page Load:** Landing page and dashboard must load within 2 seconds on 4G networks.
*   **API Response:** Application submission endpoints must respond within 500ms.
*   **File Upload:** Support upload of files up to 5MB within 10 seconds.
*   **Encryption:** All data in transit must be encrypted via TLS 1.3.
*   **Data Protection:** Student PII (National ID, Passport) must be encrypted at rest or strictly access-controlled.
*   **Input Sanitization:** All form inputs must be sanitized to prevent SQL Injection and XSS attacks.
*   **Peak Load:** System must support up to 1,000 concurrent users during the application deadline week.
*   **Availability:** 99.9% uptime required during the 2-month admission window.
*   **Recovery:** Database point-in-time recovery (PITR) enabled with a Recovery Point Objective (RPO) of < 15 minutes.
*   **Resilience:** M-Pesa integration must handle timeouts gracefully and support transaction reconciliation.
*   **Idempotency:** Webhook endpoints must handle duplicate events without data corruption.
*   **Compliance:** UI must adhere to WCAG 2.1 Level AA standards (Color contrast, Alt text, Keyboard nav).

### Additional Requirements

**From Architecture:**
-   **Starter Template:** Initialize using Laravel 11 + Breeze (Blade) via Sail.
-   **Security:** Implement PII protection using Laravel Encrypted Casting and Blind Indexing for searchability.
-   **Integration:** Secure ASP API Sync using Laravel Sanctum (API Tokens).
-   **Infrastructure:** Use Local Private Volume abstraction via `Storage` Facade for secure document storage (MVP).
-   **Structure:** Follow "Monolithic Web Application" structure with `app/Services` for business logic and Blade Components for UI.
-   **Naming/Patterns:** Adhere to strict naming conventions (e.g., snake_case DB, kebab-case API) and use Eloquent API Resources for all API outputs.

**From UX Design:**
-   **Platform:** Mobile-first responsive design strategy.
-   **Design Direction:** Implement "Card-Based Dashboard" (The App Dashboard) direction.
-   **Visuals:** Use TailwindCSS v4 with "Instrument Sans" typography and "Royal Blue" brand color.
-   **Components:** Implement specific reusable components: `ImageUploader` (camera-first), `StatusBadge`, and `MpesaReceipt`.
-   **Flow:** Support "One-Sitting Application" (under 15 mins) with "Optimistic UI" feedback.

### FR Coverage Map

FR1: Epic 1 - Registration via Email/Phone
FR2: Epic 1 - OTP Identity Verification
FR3: Epic 1 - Secure Login/Logout
FR4: Epic 1 - Route Protection
FR5: Epic 2 - New Application Initiation
FR6: Epic 2 - Draft Save Functionality
FR7: Epic 2 - Personal Information Input
FR8: Epic 2 - Parent/Guardian Info Input
FR9: Epic 2 - Program Selection
FR10: Epic 3 - Submission Rules
FR11: Epic 2 - Document Upload
FR12: Epic 2 - Document Viewing
FR13: Epic 2 - File Validation
FR14: Epic 3 - M-Pesa STK Push
FR15: Epic 3 - Manual Transaction ID
FR16: Epic 3 - M-Pesa Callback Processing
FR17: Epic 4 - ASP Pending Query
FR18: Epic 4 - ASP Status Update
FR19: Epic 4 - API Audit Logging
FR20: Epic 1 - Welcome Email
FR21: Epic 4 - Status Change Notification
FR22: Epic 5 - Admin Payment Verification
FR23: Epic 5 - Student Dashboard
FR24: Epic 5 - Grades and Schedule

## Epic List

### Epic 1: Foundation & Identity Management
Users can securely register, verify identity via OTP, and manage their session, establishing the secure entry point for the portal.
**FRs covered:** FR1, FR2, FR3, FR4, FR20

### Epic 2: Student Application Management
Applicants can complete the multi-step admission form (Personal, Parent, Program), upload required documents, and save progress as a draft.
**FRs covered:** FR5, FR6, FR7, FR8, FR9, FR11, FR12, FR13

### Epic 3: Payment Integration & Submission
Applicants can securely pay the admission fee via M-Pesa (STK Push or Manual) and formally submit their completed application.
**FRs covered:** FR10, FR14, FR15, FR16

### Epic 4: Admissions Integration & Review
The internal ASP system can sync 'Pending' applications and push 'Approved/Rejected' status updates back to the portal to keep students informed.
**FRs covered:** FR17, FR18, FR19, FR21

### Epic 5: Admin & Student Portal Features
Implement the "Missing Link" for manual payment verification and expand the portal to support the post-admission student experience.
**FRs covered:** FR22, FR23, FR24

## Epic 1: Foundation & Identity Management

Users can securely register, verify identity via OTP, and manage their session, establishing the secure entry point for the portal.

### Story 1.1: Install & Configure Breeze (Blade)

As a System Admin,
I want the Laravel Breeze (Blade) stack installed,
So that I have the standard Login/Register UI views and controllers.

**Acceptance Criteria:**

**Given** the existing `routes/auth.php` contains API routes
**When** I install Breeze
**Then** the existing API routes are preserved (renamed to `routes/api_auth.php`)
**And** `routes/auth.php` is replaced with standard Breeze Web routes
**And** `resources/views/auth/*.blade.php` files exist and render correctly

### Story 1.2: PII Encryption Schema Migration

As a Compliance Officer,
I want `national_id` and `passport_number` columns to support encryption and blind indexing,
So that student data is secure at rest.

**Acceptance Criteria:**

**Given** the existing `students` table
**When** I run the new migration
**Then** `national_id` and `passport_number` columns are modified to `TEXT`
**And** new columns `national_id_index` and `passport_number_index` are added for searching
**And** the database schema supports encryption at rest

### Story 1.3: Web-Based OTP Integration

As a Student,
I want to verify my phone/email via OTP during the web registration process,
So that my identity is confirmed.

**Acceptance Criteria:**

**Given** a user registers on the web
**When** they submit the form
**Then** they are redirected to an "Enter OTP" screen
**And** they cannot log in until OTP is verified
**And** OTP logic reuses the existing `otps` table

## Epic 2: Student Application Management

Applicants can complete the multi-step admission form (Personal, Parent, Program), upload required documents, and save progress as a draft.

### Story 2.1: Dashboard & Application Initialization

As a Applicant,
I want to start a new application from my dashboard,
So that I can begin the admission process.

**Acceptance Criteria:**

**Given** I am logged in
**When** I click "Apply Now" on the dashboard
**Then** a new record is created in `applications` table with status 'draft'
**And** I am redirected to the first step of the application wizard
**And** I can see my application status on the dashboard

### Story 2.2: Personal & Parent Info Forms

As a Applicant,
I want to fill in my personal and parent details,
So that the university has my contact and background information.

**Acceptance Criteria:**

**Given** I am on the application form
**When** I enter my details and click "Next"
**Then** the data is saved to `students` and `parent_info` tables
**And** validation rules ensure required fields are present (except for draft mode)
**And** PII fields are encrypted upon saving

### Story 2.3: Program Selection Logic

As a Applicant,
I want to select my desired program,
So that I can apply for the correct course.

**Acceptance Criteria:**

**Given** I am on the Program Selection step
**When** I choose a program from the list
**Then** the `program_id` is updated in my application record
**And** the list is populated from the existing `programs` table

### Story 2.4: Document Upload Component & Storage

As a Applicant,
I want to upload my transcripts and ID documents,
So that I can provide proof of my qualifications.

**Acceptance Criteria:**

**Given** I am on the Documents step
**When** I upload a file (PDF/Image)
**Then** the file is stored in `storage/app/private` (not public)
**And** a record is created in `documents` table linked to my application
**And** I can see a preview or link to the uploaded file
**And** file size/type validation prevents invalid uploads

## Epic 3: Payment Integration & Submission

Applicants can securely pay the admission fee via M-Pesa (STK Push or Manual) and formally submit their completed application.

### Story 3.1: M-Pesa STK Push Integration (Web)

As a Applicant,
I want to initiate an M-Pesa payment from the portal,
So that I can pay the admission fee conveniently.

**Acceptance Criteria:**

**Given** I am on the Payment step
**When** I enter my phone number and click "Pay"
**Then** an STK Push is triggered via `MpesaService`
**And** the UI shows a "Waiting for confirmation" spinner
**And** I receive a prompt on my phone to enter my PIN

### Story 3.2: Manual Payment Fallback

As a Applicant,
I want to manually enter a Transaction ID if the automated payment fails,
So that I can still complete my application.

**Acceptance Criteria:**

**Given** STK push fails or I choose "Manual Payment"
**When** I pay via Paybill and enter the M-Pesa Transaction ID
**And** I upload a screenshot of the payment message
**Then** the payment details are recorded in `payments` table with status 'pending_verification'

### Story 3.3: Final Submission Logic

As a Applicant,
I want to submit my completed application,
So that it can be reviewed by the university.

**Acceptance Criteria:**

**Given** all steps are complete and payment is recorded
**When** I click "Submit Application"
**Then** the application status changes to 'submitted'
**And** I receive a confirmation email
**And** I cannot edit the application anymore

### Story 3.4: Payment Callback Handling

As a System,
I want to process M-Pesa callbacks automatically,
So that payments are verified in real-time.

**Acceptance Criteria:**

**Given** M-Pesa sends a callback to the callback URL
**When** the system receives the payload
**Then** `PaymentController` validates the signature
**And** updates the corresponding `payments` record status
**And** updates the `applications` status if payment is successful

## Epic 4: Admissions Integration & Review

The internal ASP system can sync 'Pending' applications and push 'Approved/Rejected' status updates back to the portal to keep students informed.

### Story 4.1: ASP API Authentication (Sanctum)

As a System Admin,
I want to secure the ASP sync endpoints,
So that only the authorized ASP system can access student data.

**Acceptance Criteria:**

**Given** the ASP system needs to connect
**When** it presents a valid Sanctum token with `asp:sync` scope
**Then** the request is authorized
**And** invalid tokens are rejected with 401 Unauthorized

### Story 4.2: Pending Applications Endpoint

As a System Integration,
I want to query for all 'Pending' applications,
So that I can import them into the internal ASP database.

**Acceptance Criteria:**

**Given** new applications have been submitted
**When** I GET `/api/v1/sync/pending`
**Then** I receive a JSON list of applications with status 'submitted'
**And** PII data is decrypted for the response
**And** the access is logged to `api_logs`

### Story 4.3: Status Update Endpoint

As a System Integration,
I want to push status updates (Approved/Rejected) back to the portal,
So that the student sees the latest decision.

**Acceptance Criteria:**

**Given** an admission decision is made in ASP
**When** I POST to `/api/v1/sync/status` with `{ "application_id": 1, "status": "approved" }`
**Then** the local application status is updated
**And** the student receives an email notification via the existing notification service

### Story 4.4: Sync Resilience & Logging

As a Auditor,
I want a full log of all sync activities,
So that I can troubleshoot failures and track data movement.

**Acceptance Criteria:**

**Given** any sync API request (Success or Fail)
**When** the request completes
**Then** a record is written to `api_logs` with timestamp, IP, endpoint, and response code
**And** status changes are recorded in `status_histories`

## Epic 5: Admin & Student Portal Features

Implement the "Missing Link" for manual payment verification and expand the portal to support the post-admission student experience.

### Story 5.1: Admin Payment Verification Panel

As a Finance Admin,
I want to view and approve manual payment receipts,
So that applications with manual payments can proceed to the "Submitted" state and be synced to ASP.

**Acceptance Criteria:**

- [ ] Admin route `/admin/payments` created (protected by middleware).
- [ ] List all payments with status `pending_verification`.
- [ ] View details: Student Name, Transaction Code, Proof Image.
- [ ] Action: "Approve" -> Sets payment status `paid` -> Triggers `ApplicationService::submit` (if app was waiting).
- [ ] Action: "Reject" -> Sets payment status `failed` -> Email user to retry.

### Story 5.2: Student Dashboard Transformation

As a Approved Student,
I want to see a dedicated Student Dashboard instead of the Admission Status,
So that I can access my student services.

**Acceptance Criteria:**

- [ ] Logic: If `application.status` == `approved`, redirect `/dashboard` to specific Student View.
- [ ] View: Display "Welcome, [Student Name]".
- [ ] View: Display Student ID (synced from ASP).
- [ ] Navigation: Links to "My Grades", "Class Schedule", "Fee Statement".

### Story 5.3: Student Information Service (Mock Strategy)

As a Developer,
I want a decoupled service for fetching student data,
So that I can build the UI now using Mock data and swap to Realtime ASP API later.

**Acceptance Criteria:**

- [ ] Interface: `StudentInformationServiceInterface`.
- [ ] Implementation: `MockStudentInformationService` (returns hardcoded JSON).
- [ ] Binding: Bind interface to implementation in `AppServiceProvider`.
- [ ] Config: Toggle in `.env` (`STUDENT_INFO_DRIVER=mock`).

### Story 5.4: View Grades & Schedule

As a Student,
I want to view my grades and class schedule,
So that I can plan my academic activities.

**Acceptance Criteria:**

- [ ] Page: `/student/grades` lists courses and grades (from Service).
- [ ] Page: `/student/schedule` lists weekly timetable (from Service).
- [ ] Page: `/student/fees` lists outstanding balance (from Service).
