---
stepsCompleted:
  - step-01-init
  - step-02-discovery
  - step-03-success
  - step-04-journeys
  - step-05-domain
  - step-06-innovation
  - step-07-project-type
  - step-08-scoping
  - step-09-functional
  - step-10-nonfunctional
  - step-11-polish
classification:
  projectType: Web Application
  domain: EdTech
  complexity: Medium
  projectContext: brownfield
inputDocuments:
  - _bmad-output/index.md
  - _bmad-output/project-overview.md
  - _bmad-output/integration-architecture.md
  - _bmad-output/architecture-student-admission-portal.md
  - _bmad-output/source-tree-analysis.md
  - _bmad-output/development-guide-student-admission-portal.md
  - _bmad-output/ui-component-inventory-student-admission-portal.md
  - _bmad-output/data-models-student-admission-portal.md
  - _bmad-output/api-contracts-student-admission-portal.md
documentCounts:
  briefCount: 0
  researchCount: 0
  brainstormingCount: 0
  projectDocsCount: 9
workflowType: 'prd'
---

# Product Requirements Document - blood-group-student-port

**Author:** Wavister
**Date:** 2026-01-20

## Success Criteria

### User Success

*   **Applicants:** Can complete the 4-step application form and pay via M-Pesa in under 15 minutes. Receive immediate confirmation.
*   **Staff:** Review applications in ASP with status updates automatically reflected in the Portal.

### Business Success

*   **Efficiency:** 100% automated synchronization of 'Pending' applications to ASP.
*   **Revenue:** Accurate M-Pesa payment reconciliation via Transaction ID matching.

### Technical Success

*   **Integration:** Reliable bi-directional API communication (Portal -> ASP -> Portal).
*   **Security:** Secure storage of student documents and PII.

### Measurable Outcomes

*   Successful application submission rate > 90%.
*   M-Pesa payment success rate > 95%.
*   Sync latency < 5 minutes (depending on cron schedule).

## Project Scoping & Phased Development

### MVP Strategy & Philosophy

**MVP Approach:** Brownfield Refactoring & Extension. The goal is to stabilize and extend the existing `student-admission-portal` codebase to ensure reliable admission processing.
**Resource Requirements:** 1 Full-Stack Developer (Laravel) for refactoring and feature completion.

### MVP Feature Set (Phase 1)

**Core User Journeys Supported:**
*   Student Application & Payment (Refactor existing flow)
*   Staff Approval via Sync (Verify/Fix existing ASP integration)

**Must-Have Capabilities (Gap Analysis):**
*   **Audit/Fix:** Secure User Auth (OTP).
*   **Refactor:** 4-Stage Multi-part Form (Ensure state handling matches new specs).
*   **Verify:** File Upload (Images/PDF) to private storage (Check current implementation).
*   **Fix/Test:** M-Pesa STK Push Integration (Validate existing `PaymentService`).
*   **Optimize:** Cron-based Bi-directional Sync with ASP (Review `SyncPendingApplications` command).

### Post-MVP Features

**Phase 2 (Growth):**
*   Real-time notifications (WebSockets).
*   Advanced Document Preview in Portal.
*   Support Ticket System.

**Phase 3 (Expansion):**
*   Mobile App (Flutter/React Native).
*   AI-powered document verification.

### Risk Mitigation Strategy

**Technical Risks:** M-Pesa API failures. **Mitigation:** Robust logging and manual retry button in Admin panel.
**Market Risks:** Low adoption due to complexity. **Mitigation:** 'How to Apply' video guide on landing page.
**Resource Risks:** Timeline slippage. **Mitigation:** Cut 'Student Dashboard' post-admission features from MVP if needed (focus only on Application).

## User Journeys

### 1. Minh - The Aspiring Student (Primary User)
*   **Scene:** Minh is anxious about the admission deadline. He visits the portal on his laptop.
*   **Action:** He registers with his email and receives an OTP instantly. Relieved, he logs in and sees the "Apply Now" button.
*   **Process:** He breezily fills in his personal details and parent's info. He selects "Computer Science".
*   **Climax:** He reaches the payment step. He follows the instructions to Paybill via M-Pesa on his phone. He enters the transaction code.
*   **Resolution:** He hits "Submit". The system immediately confirms "Application Received". Minh sighs with relief.
*   **Post-Credit:** A few days later, he receives an email "Application Approved!". He logs in to find his Student Code and Class Schedule waiting on the dashboard.

### 2. Ms. Lan - The Admissions Officer (Admin/ASP User)
*   **Scene:** Ms. Lan starts her day at the university office. She opens the internal ASP system.
*   **Action:** She clicks "Sync from Portal". The system pulls in new applications, including Minh's.
*   **Process:** She opens Minh's record. She verifies his high school transcript and ID photo. Everything looks good.
*   **Climax:** She clicks "Approve".
*   **Resolution:** The ASP system automatically pushes this status back to the Student Portal. Ms. Lan moves to the next application, trusting the systems are in sync.

### 3. The Integration Bot (System/Technical User)
*   **Scene:** It's 2:00 AM. The Cron Job wakes up.
*   **Action:** It queries the Portal API: `GET /api/v1/students?status=pending`.
*   **Process:** It retrieves a JSON payload of pending applications. It validates the data structure.
*   **Resolution:** It successfully imports them into the ASP database and logs the transaction.

### Journey Requirements Summary

*   **Registration:** OTP generation/validation.
*   **Application:** Multi-step form state management, file upload.
*   **Payment:** M-Pesa API integration.
*   **Sync:** API endpoints for GET pending and POST status updates.
*   **Notification:** Email service integration.

## Functional Requirements

### Authentication & Identity

*   **FR1:** User can register using an email address or phone number.
*   **FR2:** User can verify their identity via a One-Time Password (OTP).
*   **FR3:** User can log in and log out of the portal securely.
*   **FR4:** System must block access to protected routes for unauthenticated users.

### Application Management

*   **FR5:** User can initiate a new admission application.
*   **FR6:** User can save application progress as a 'Draft'.
*   **FR7:** User can input Personal Information (Name, DOB, ID).
*   **FR8:** User can input Parent/Guardian Information.
*   **FR9:** User can select a Program/Course from a predefined list.
*   **FR10:** User can submit the application only after all steps and payment are complete.

### Document Management

*   **FR11:** User can upload digital documents (Images, PDFs) for required artifacts (ID, Transcript).
*   **FR12:** User can view their uploaded documents to confirm correctness.
*   **FR13:** System must validate file types and size limits before acceptance.

### Payment Processing

*   **FR14:** User can initiate an M-Pesa payment request (STK Push).
*   **FR15:** User can manually enter a Transaction ID if STK Push fails (optional fallback).
*   **FR16:** System must receive and process M-Pesa payment callbacks to update application status.

### Integration & Synchronization

*   **FR17:** External System (ASP) can query the Portal for 'Pending' applications via API.
*   **FR18:** External System (ASP) can update the status of an application (Approved/Rejected/Request Info) via API.
*   **FR19:** System must log all API requests for audit purposes.

### Notifications

*   **FR20:** System must send an email confirmation upon successful registration.
*   **FR21:** System must send an email notification when Application Status changes.

## Non-Functional Requirements

### Performance

*   **Page Load:** Landing page and dashboard must load within 2 seconds on 4G networks.
*   **API Response:** Application submission endpoints must respond within 500ms.
*   **File Upload:** Support upload of files up to 5MB within 10 seconds.

### Security

*   **Encryption:** All data in transit must be encrypted via TLS 1.3.
*   **Data Protection:** Student PII (National ID, Passport) must be encrypted at rest or strictly access-controlled.
*   **Input Sanitization:** All form inputs must be sanitized to prevent SQL Injection and XSS attacks.

### Scalability & Reliability

*   **Peak Load:** System must support up to 1,000 concurrent users during the application deadline week.
*   **Availability:** 99.9% uptime required during the 2-month admission window.
*   **Recovery:** Database point-in-time recovery (PITR) enabled with a Recovery Point Objective (RPO) of < 15 minutes.

### Integration

*   **Resilience:** M-Pesa integration must handle timeouts gracefully and support transaction reconciliation.
*   **Idempotency:** Webhook endpoints must handle duplicate events without data corruption.

### Accessibility

*   **Compliance:** UI must adhere to WCAG 2.1 Level AA standards (Color contrast, Alt text, Keyboard nav).

## Domain-Specific Requirements

### Compliance & Regulatory

*   **Data Privacy:** Strict separation of student PII and academic records.
*   **Financial Audit:** All M-Pesa transactions logged with `CheckoutRequestID` and `MerchantRequestID`.

### Technical Constraints

*   **Security:**
    *   Uploaded documents stored outside public web root.
    *   API access restricted to authorized ASP IPs/Keys.
*   **Reliability:**
    *   Idempotent M-Pesa callback handling.

### Integration Requirements

*   **M-Pesa:** STK Push + Callback.
*   **ASP (Academic Service Platform):**
    *   `GET /students` (Poll pending).
    *   `POST /update-status` (Push status).

### Risk Mitigations

*   **Sync Failure:** Queue status updates or retry if ASP is down.
*   **Payment Mismatch:** Automated reconciliation script.

## Web Application Specific Requirements

### Project-Type Overview

The application is a standard Monolithic Web Application built with Laravel 11. It utilizes server-side rendering (Blade) for the majority of views, enhanced with TailwindCSS for styling and minimal JavaScript for interactivity. This approach simplifies development and maintenance for the internal team.

### Technical Architecture Considerations

*   **Structure:** Multi-Page Application (MPA) using Laravel Blade Templates.
*   **Frontend:** TailwindCSS v4 for styling, Vite for asset bundling.

### Browser & Device Support

*   **Browser Matrix:** Latest versions of Chrome, Firefox, Safari, Edge.
*   **Responsive Design:** Mobile-first approach. Critical for students applying via smartphones (Android/iOS).

### Performance Targets

*   **Page Load:** < 2 seconds for Landing and Dashboard.
*   **Submission Processing:** < 3 seconds for form submission.

### SEO Strategy

*   **Public Pages:** Landing page metadata (Title, Description, OpenGraph) for social sharing.
*   **Protected Pages:** `noindex` headers to prevent crawling of student data.

### Accessibility

*   **Standard:** WCAG 2.1 Level AA (Target).
*   **Focus:** Readable fonts (Instrument Sans), high contrast for forms, keyboard navigation support.
