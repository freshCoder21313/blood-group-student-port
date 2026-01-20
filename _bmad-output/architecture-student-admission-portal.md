# Architecture - Student Admission Portal

## Executive Summary
The Student Admission Portal is a web-based system designed to digitize the university admission process. It serves as a student-facing frontend that integrates with an internal Academic Service Platform (ASP). The system handles student registration, multi-step application forms, document uploads, and fee payments via M-Pesa.

## Architectural Pattern
**Layered Monolithic MVC**
The application is built on the Laravel framework, following the Model-View-Controller (MVC) pattern, enhanced with a Service Layer to encapsulate business logic and external integrations.

- **Presentation Layer:** Laravel Blade templates (hybrid) and JSON APIs.
- **Application Layer:** Controllers (`app/Http/Controllers`) handle HTTP requests and validation.
- **Service Layer:** (`app/Services`) contains domain logic, payment processing, and external API clients.
- **Domain Layer:** Eloquent Models (`app/Models`) represent business entities.
- **Persistence Layer:** Database (MySQL/SQLite).

## Technology Stack
- **Backend:** PHP 8.2, Laravel 11
- **Frontend:** Blade, TailwindCSS 4, Vite
- **Database:** SQLite (Dev), MySQL/Postgres (Prod)
- **Queue System:** Laravel Queue (database driver)
- **External Services:** Safaricom M-Pesa, ASP (Academic Service Platform)

## Key Components

### 1. Application Management
Handles the lifecycle of an admission application:
`Draft -> Pending Payment -> Pending Approval -> Approved/Rejected`

### 2. Payment Gateway
Integrates with Safaricom M-Pesa via STK Push. Uses a callback mechanism to confirm payments asynchronously.

### 3. ASP Integration
A critical component that bridges the public portal with the internal university system.
- **Syncs:** Grades, Timetables, Fees.
- **Mechanism:** HMAC-signed API requests.

## Security
- **Authentication:** Laravel Sanctum (API Tokens) and Session-based auth.
- **Authorization:** Middleware-based protection.
- **API Security:** Requests to ASP are signed using `HMAC-SHA256` to ensure integrity and authenticity.

## Deployment Architecture
Designed for containerized deployment (Docker) or standard LAMP/LEMP stack.
- **Web Server:** Nginx/Apache
- **App Server:** PHP-FPM
- **Database:** Managed SQL instance
- **Worker:** Supervisor process for queues
