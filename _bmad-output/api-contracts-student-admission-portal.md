# API Contracts - Student Admission Portal

## Overview
The Student Admission Portal API provides endpoints for student registration, application management, payment processing, and integration with the external ASP system.

**Base URL:** `/api`
**Authentication:** Bearer Token (Sanctum)

## Authentication

### Login
- **Endpoint:** `POST /v1/auth/login`
- **Description:** Authenticates a user and returns an access token.
- **Request Body:**
  ```json
  {
    "email": "student@example.com",
    "password": "password",
    "device_name": "web_app"
  }
  ```
- **Response:**
  ```json
  {
    "success": true,
    "data": {
      "user": { ... },
      "token": "1|laravel_sanctum_token...",
      "token_type": "Bearer"
    }
  }
  ```

### Register
- **Endpoint:** `POST /v1/auth/register`
- **Description:** Registers a new student account.

### OTP Verification
- **Endpoint:** `POST /v1/auth/otp/send`
- **Endpoint:** `POST /v1/auth/otp/verify`

## Student & Application Management

### List Students (or Applications)
- **Endpoint:** `GET /v1/students`
- **Query Params:** `status` (draft, pending_payment, etc.), `page`, `per_page`
- **Response:** Paginated list of student applications.

### Get Application Detail
- **Endpoint:** `GET /v1/students/{id}`
- **Description:** Returns full application details including student info, steps, documents, and payment status.

### Documents
- **Endpoint:** `GET /v1/students/{id}/documents`
- **Endpoint:** `GET /v1/documents/{id}/download`

## Payments (M-Pesa)

### Initiate Payment
- **Endpoint:** `POST /v1/payments/initiate`
- **Description:** Triggers an M-Pesa STK Push.
- **Request Body:**
  ```json
  {
    "application_id": 123,
    "phone_number": "254700000000",
    "amount": 1000
  }
  ```

### Payment Callback
- **Endpoint:** `POST /v1/payments/callback`
- **Description:** M-Pesa Webhook endpoint.

## ASP Integration (External Data)

### Get Grades
- **Endpoint:** `GET /v1/students/{student_code}/grades`
- **Description:** Proxies request to ASP system to fetch student grades.

### Get Timetable
- **Endpoint:** `GET /v1/students/{student_code}/timetable`

### Get Fees
- **Endpoint:** `GET /v1/students/{student_code}/fees`

## Webhooks (From ASP)

### Status Changed
- **Endpoint:** `POST /v1/webhooks/status-changed`
- **Description:** Updates application status when changed in ASP.

### Grade Updated
- **Endpoint:** `POST /v1/webhooks/grade-updated`
