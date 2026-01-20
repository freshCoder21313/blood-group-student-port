# Data Models - Student Admission Portal

## Schema Overview
The database handles student profiles, multi-step applications, document storage, and payment transactions.

## Core Entities

### Student
- **Table:** `students`
- **Description:** Core profile information.
- **Key Fields:**
  - `user_id`: FK to users table
  - `student_code`: Unique identifier (from ASP?)
  - `first_name`, `last_name`: Personal info
  - `date_of_birth`
  - `national_id`: **Encrypted (TEXT)**
  - `national_id_index`: **Blind Index (String)** for searching
  - `passport_number`: **Encrypted (TEXT)**
  - `passport_number_index`: **Blind Index (String)** for searching
  - `profile_photo`: Path to photo

### Application
- **Table:** `applications`
- **Description:** Represents a student's admission application.
- **Key Fields:**
  - `student_id`: FK to students
  - `program_id`: FK to programs
  - `block_id`: FK to academic_blocks
  - `application_number`: Unique reference
  - `status`: Enum (draft, pending_payment, pending_approval, request_info, approved, rejected)
  - `current_step`: Tracking progress (1-4)
  - `payment_status`: (Inferred/related)

### Document
- **Table:** `documents`
- **Description:** Files uploaded by the student.
- **Key Fields:** `application_id`, `type` (transcript, id_card, etc.), `path`, `status`.

### Payment
- **Table:** `payments`
- **Description:** M-Pesa transaction records.
- **Key Fields:**
  - `application_id`: FK
  - `checkout_request_id`: M-Pesa identifier
  - `merchant_request_id`: M-Pesa identifier
  - `transaction_ref`: Internal reference
  - `amount`, `phone_number`
  - `status`: pending, completed, failed
  - `mpesa_receipt_number`: From callback

### StatusHistory
- **Table:** `status_histories`
- **Description:** Audit log of application status changes.

## Relationships
- **User** 1:1 **Student**
- **Student** 1:1 **Application** (Current design seems 1:1, but could be 1:M)
- **Application** 1:M **Documents**
- **Application** 1:1 **Payment**
- **Student** 1:1 **ParentInfo**

## External Integration
- **ApiLog**: Stores logs of requests made to/from ASP system for debugging and auditing.
