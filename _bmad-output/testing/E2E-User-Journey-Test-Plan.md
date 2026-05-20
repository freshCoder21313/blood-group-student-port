
# End-to-End User Journey Test Plan

**Objective:** To validate the complete student admission workflow from registration to post-enrollment, ensuring all system components function and integrate as per the requirements in `temp_plans/origin.md` and the PRD.

**Test Environment Setup:**

1.  **Database:** Ensure the database is migrated to the latest version.
    ```bash
    php artisan migrate:fresh
    ```
2.  **Seed Data:** Run the database seeder to create necessary initial data (programs, admin user).
    ```bash
    php artisan db:seed
    ```
3.  **Mail Server:** Use a local mail server like MailHog or Mailtrap to capture and inspect emails sent by the application (Registration, OTP, Confirmation, Status Updates).
4.  **ASP Mock:** Since the real ASP system is external, we will simulate its behavior by manually calling the API endpoints using a tool like `curl` or Postman.

---

## Test Cases

### Phase 1: Registration & Application (PHP Portal)

**User Persona:** Minh (New Applicant)
**Goal:** Successfully register, fill out the application, pay, and submit the application.

| Step | Action | Expected Result | Status |
| :--- | :--- | :--- | :--- |
| **TC1.1** | **Registration** | | |
| 1.1.1 | Navigate to the `/register` page. | Registration form is displayed. | `[ ]` |
| 1.1.2 | Fill in the registration form with a new email (e.g., `minh.applicant@example.com`) and phone number. | User is created. System sends an OTP email. User is redirected to the OTP verification page (`/otp/verify`). | `[ ]` |
| 1.1.3 | Check MailHog/Mailtrap for the OTP email. | OTP email is received. | `[ ]` |
| 1.1.4 | Enter the correct OTP on the verification page. | OTP is verified. User is logged in and redirected to the `/dashboard`. | `[ ]` |
| 1.1.5 | On the dashboard, the user's status is "New". An "Apply Now" button is visible. | Dashboard shows correct initial state. | `[ ]` |
| **TC1.2** | **Application Form (Wizard)** | | |
| 1.2.1 | Click "Apply Now". | A new application is created. User is redirected to the application wizard (`/application/{id}/wizard`). | `[ ]` |
| 1.2.2 | **Step 1: Personal Info** - Fill in all required personal details and save. | Data is saved. The wizard progresses to Step 2. | `[ ]` |
| 1.2.3 | **Step 2: Parent Info** - Fill in parent/guardian details and save. | Data is saved. The wizard progresses to Step 3. | `[ ]` |
| 1.2.4 | **Step 3: Program Selection** - Select a program and academic block from the dropdowns and save. | Data is saved. The wizard progresses to Step 4. | `[ ]` |
| 1.2.5 | **Step 4: Document Upload** - Upload required documents (e.g., a dummy PDF for transcript, a dummy JPG for photo). | Files are uploaded successfully. Previews or file names are shown. The "Proceed to Payment" button is enabled. | `[ ]` |
| **TC1.3** | **Payment & Submission** | | |
| 1.3.1 | Click "Proceed to Payment". | User is redirected to the payment page, which shows payment instructions (Paybill/Till number). | `[ ]` |
| 1.3.2 | **Manual Payment:** Enter a fake M-Pesa transaction code (e.g., `TESTABC123`) and upload a dummy payment proof image. | The manual payment details are recorded. | `[ ]` |
| 1.3.3 | Click "SUBMIT APPLICATION". | Application status changes to `Pending Approval`. User is redirected to the dashboard. An email confirmation ("Application Received") is sent. | `[ ]` |
| 1.3.4 | Check MailHog/Mailtrap for the submission confirmation email. | Confirmation email is received. | `[ ]` |
| 1.3.5 | Check the `applications` table in the database for Minh's application. | The status is `pending-approval`. The payment record is associated. | `[ ]` |

---

### Phase 2: Review & Synchronization (PHP ↔ ASP)

**User Persona:** Ms. Lan (Admissions Officer) / System
**Goal:** Fetch the pending application from the portal, review it, and update its status.

| Step | Action | Expected Result | Status |
| :--- | :--- | :--- | :--- |
| **TC2.1** | **Sync Pending Applications (ASP → PHP)** | | |
| 2.1.1 | **Simulate ASP:** Generate a Sanctum token for the ASP system. | Token is generated successfully. | `[ ]` |
| 2.1.2 | **Simulate ASP:** Make an authenticated API call: `GET /api/v1/sync/pending`. | The API returns a JSON list of pending applications, including Minh's application from TC1. | `[ ]` |
| **TC2.2** | **Approve Application (ASP → PHP)** | | |
| 2.2.1 | **Simulate ASP:** Make an authenticated API call to update the status: `POST /api/v1/sync/status` with the application ID, `status: 'Approved'`, and a new `student_code: 'STUDENT-2026-001'`. | The API returns a success message. The application status in the PHP database is updated to `Approved`. The `student_code` is stored. | `[ ]` |
| 2.2.2 | Check the `applications` table. | The status is `approved` and `student_code` is set. | `[ ]` |
| 2.2.3 | Check MailHog/Mailtrap for a status update email to Minh. | An "Application Approved" email is received by Minh, containing his new Student Code. | `[ ]` |
| **TC2.3** | **Request Info (Alternative Scenario)** | | |
| 2.3.1 | (On a different application) **Simulate ASP:** Make an API call: `POST /api/v1/sync/status` with `status: 'Request Info'` and a reason. | Status is updated to `request-info` in the database. | `[ ]` |
| 2.3.2 | An email is sent to the student explaining what needs to be fixed. | Email is received. | `[ ]` |
| 2.3.3 | Log in as the student. | The dashboard should show the 'Request Info' status and allow the student to edit the application. | `[ ]` |

---

### Phase 3: Post-Enrollment (Student Portal)

**User Persona:** Minh (Now an Admitted Student)
**Goal:** View the transformed student dashboard.

| Step | Action | Expected Result | Status |
| :--- | :--- | :--- | :--- |
| **TC3.1** | **Dashboard Transformation** | | |
| 3.1.1 | Log in as Minh (`minh.applicant@example.com`). | User is redirected to the `/dashboard`. | `[ ]` |
| 3.1.2 | Verify the dashboard content. | The application form is gone. The dashboard now shows widgets for: **Grades**, **Class Schedule**, **Fee Information**. The student's name and new Student Code are displayed prominently. | `[ ]` |
| **TC3.2** | **Data Display (Mocked)** | | |
| 3.2.1 | Click on the "View Grades" link/button. | User is taken to `/student/grades`. Since the real data comes from ASP, this should display a message like "Grades are not yet available" or show data from the mock service (`StudentInformationService`). | `[ ]` |
| 3.2.2 | Click on the "View Schedule" link/button. | User is taken to `/student/schedule`. It should display mock schedule data correctly. | `[ ]` |

This test plan covers the critical user journey from start to finish. Each step should be checked off as it's completed and verified.
