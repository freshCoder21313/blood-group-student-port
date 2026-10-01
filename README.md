# Student Admission & Academic Portal (Laravel 11 + ASP.NET Integration Bridge)

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%20%7C%208.3-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net)
[![Sanctum](https://img.shields.io/badge/Security-Laravel%20Sanctum-red?style=flat-square)](https://laravel.com/docs/11.x/sanctum)
[![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.x-38B2AC?style=flat-square&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![Vite](https://img.shields.io/badge/Vite-5.x-646CFF?style=flat-square&logo=vite&logoColor=white)](https://vitejs.dev)
[![Pest Testing](https://img.shields.io/badge/Testing-Pest%20PHP-purple?style=flat-square)](https://pestphp.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=flat-square)](./LICENSE)

> **High-availability student admissions, fee processing, and academic services web platform built with Laravel 11, featuring a bi-directional, cryptographic synchronization bridge with enterprise ASP.NET Training Management & ERP backends.**

---

## 📌 Executive Summary & Problem Solved

Higher education institutions frequently suffer from fragmented IT systems: modern, responsive student-facing interfaces are disconnected from core legacy academic ERP and training management systems (often built on proprietary ASP.NET infrastructures). This fragmentation causes manual data re-entry, delayed admission approvals, asynchronous fee reconciliation, and student frustration.

**Student Admission Portal** bridges this gap by acting as a modern, high-throughput admission intake and student portal that communicates seamlessly with upstream ASP.NET systems. It features:
- A responsive, multi-step application submission portal with OTP identity verification and document uploading.
- Real-time M-Pesa payment gateway integration for automated tuition/application fee settlements.
- An enterprise-grade synchronization pipeline implementing HMAC-SHA256 request signing and Laravel Sanctum scoped token abilities to sync applicant statuses, admission blocks, academic records, and fee ledgers.

---

## 🏗️ Workflow Architecture & Synchronization Pipeline

```mermaid
flowchart TD
    subgraph Student_Portal ["1. Student & Public Portal (Laravel 11 + Blade / Tailwind)"]
        Applicant[Applicant / Student]
        RegFlow[Auth & OTP Verification<br/><i>OtpService / SMS / Email</i>]
        AppForm[Multi-Step Application Engine<br/><i>Document Upload & Barryvdh DomPDF</i>]
        Payment[Payment Gateway<br/><i>M-Pesa STK Push / Manual Processor</i>]
        StudentDash[Student Dashboard<br/><i>Grades, Timetable & Fee Statements</i>]
    end

    subgraph Laravel_Core ["2. Laravel Service & Security Layer"]
        SanctumAuth{Auth & Security Guard<br/><i>Sanctum 'asp:sync' & API Key Middleware</i>}
        HMAC{HMAC-SHA256 Verifier<br/><i>X-API-Key, X-Timestamp, X-Signature</i>}
        AppService[ApplicationService & State Machine]
        DocService[DocumentStorageService]
        MpesaServ[MpesaService & Callback Handler]
        SyncController[StudentSyncController & AspSyncController]
    end

    subgraph Sync_Bridge ["3. Bi-Directional ASP.NET Integration Bridge"]
        InboundSync[Inbound Sync API<br/><i>GET /api/v1/students<br/>POST /api/v1/update-status<br/>POST /api/v1/bulk-update-status</i>]
        OutboundProxy[Outbound Student Data Proxy<br/><i>AspStudentInformationService</i>]
        LocalMock[Local ASP Mock Server<br/><i>asp-local/LocalAspMock .NET Core</i>]
    end

    subgraph Enterprise_ASP ["4. Core Training Management System (ASP.NET Backend)"]
        ASPERP[(ASP.NET Core / SQL Server ERP Database)]
    end

    %% Flow connections
    Applicant --> RegFlow
    RegFlow --> AppForm
    AppForm --> Payment
    Payment --> MpesaServ
    AppForm --> DocService
    AppForm --> AppService

    StudentDash --> OutboundProxy
    OutboundProxy --> ASPERP

    ASPERP <===> InboundSync
    LocalMock <.- Offline Testing -.-> InboundSync

    InboundSync --> HMAC
    InboundSync --> SanctumAuth
    HMAC --> SyncController
    SanctumAuth --> SyncController
    SyncController --> AppService
```

---

## 🚀 Key Features & Detailed Capabilities

### 🎓 Student Admission Lifecycle
- **Multi-Step Digital Application**: Step-by-step submission including academic history, program choices, admission block selection, and document attachments.
- **OTP Verification & Identity**: Phone (SMS) and Email token verification with expiry and throttling safeguards.
- **Dynamic PDF Generation**: Generates downloadable admission letters and completed application forms on the fly via `barryvdh/laravel-dompdf`.
- **Payment Processing**: Integrated M-Pesa STK Push API with automated asynchronous callback reconciliation, alongside manual bank slip verification.

### 🔄 Bi-Directional ASP.NET Enterprise Synchronization
- **Cryptographic Authentication**: Inbound ASP.NET requests are authenticated using HMAC-SHA256 signatures (`X-API-Key`, `X-Timestamp`, `X-Signature`) with replay attack prevention.
- **Sanctum Scoped Token Access**: Fine-grained token abilities (`asp:sync`) for administrative endpoints.
- **Batch Status Synchronization**: Ingests bulk approval/rejection updates from admissions officers working inside the ASP.NET system.
- **Academic Data Proxying**: Fetches live student transcripts, term grades, lecture timetables, and fee ledgers directly from the ASP backend on demand.
- **Offline Mocking Suite**: Includes a dedicated C# `.NET` mock backend (`asp-local/LocalAspMock`) with unit tests for isolated local development and CI testing.

---

## 🛠️ Tech Stack & System Requirements

### Backend & Core
- **Framework**: Laravel 11.x
- **Language**: PHP 8.2 or PHP 8.3
- **Database**: MySQL 8.0+, MariaDB 10.11+, or SQLite (for local/testing)
- **Authentication**: Laravel Sanctum 4.x
- **PDF Engine**: Barryvdh Laravel DomPDF 3.1
- **Testing**: Pest PHP 4.x, Mockery 1.6

### Frontend & Tooling
- **CSS Framework**: Tailwind CSS 3.x with PostCSS
- **Bundler**: Vite 5.x
- **Task Runner**: Concurrently (multi-process server, queue, logs, and vite)

### System Requirements & PHP Extensions
- Required PHP Extensions: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`
- Composer 2.x
- Node.js 18+ & npm 9+

---

## 📦 Installation & Setup Guide

### 1. Clone the Repository

```bash
git clone https://github.com/your-org/blood-group-student-port.git
cd blood-group-student-port/student-admission-portal
```

### 2. Install PHP & Node Dependencies

```bash
composer install
npm install
```

### 3. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Configure your `.env` file with database credentials and ASP.NET integration keys:

```ini
APP_NAME="Student Admission Portal"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=student_admission
DB_USERNAME=root
DB_PASSWORD=your_secure_password

# ASP.NET ERP Integration Settings
ASP_API_BASE_URL=https://internal-asp.school.local/api
ASP_API_KEY=your_asp_api_key
ASP_API_SECRET=your_asp_hmac_secret
ASP_SYNC_ENABLED=true

# M-Pesa Payment Gateway (Optional)
MPESA_ENVIRONMENT=sandbox
MPESA_CONSUMER_KEY=your_consumer_key
MPESA_CONSUMER_SECRET=your_consumer_secret
MPESA_SHORTCODE=174379
MPESA_PASSKEY=your_passkey
```

### 4. Database Migration & Seeding

```bash
# Run database schema migrations
php artisan migrate

# Seed sample academic programs, admission blocks, and admin users
php artisan db:seed
```

### 5. Compile Frontend Assets

```bash
npm run build
```

---

## 💻 Running the Application

### Development Mode (All Services Concurrently)
Laravel 11 Composer scripts include a unified development command that launches the web server, queue worker, real-time log streamer (Pail), and Vite hot-reloading:

```bash
composer run dev
```

Alternatively, run services individually:
```bash
# Web server
php artisan serve

# Vite asset bundler
npm run dev

# Queue worker
php artisan queue:work
```

The portal will be accessible at: `http://localhost:8000`

---

## 🔌 API Reference & Integration Endpoints

All API endpoints are prefixed with `/api/v1`.

### 1. Authentication & Candidate Endpoints (Public)

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `POST` | `/api/register` | Register new student candidate account |
| `POST` | `/api/login` | Authenticate and obtain Sanctum Bearer token |
| `POST` | `/api/verify-otp` | Validate SMS/Email one-time passcode |

### 2. Inbound ASP.NET Sync Endpoints

> **Required Headers for HMAC Authentication:**
> `X-API-Key: <ASP_API_KEY>`  
> `X-Timestamp: <Unix_Epoch_Timestamp>`  
> `X-Signature: <HMAC_SHA256_Hash>`

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/v1/students` | Retrieve submitted applications (filter by `status`, `date_from`, `page`, `per_page`) |
| `GET` | `/api/v1/students/{id}` | Get full application details with attached documents |
| `POST` | `/api/v1/update-status` | Update single application status (`approved`, `rejected`, `conditional`) |
| `POST` | `/api/v1/bulk-update-status` | Batch update application statuses from admissions board |
| `POST` | `/api/v1/students/{code}/academic-records` | Push confirmed student admission & academic record from ASP |

### 3. Student Academic Data (Proxy to ASP Backend)

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/v1/students/{code}/grades` | Fetch term grades & GPA history from ASP |
| `GET` | `/api/v1/students/{code}/timetable` | Fetch enrolled course schedule & venues |
| `GET` | `/api/v1/students/{code}/fees` | Fetch fee statements, balances, and payment invoices |

---

## 🧪 Testing Suite

The repository utilizes **Pest PHP** for behavioral testing and unit assertions:

```bash
# Run full automated test suite
composer test

# Or run Pest directly
./vendor/bin/pest

# Run with coverage report
./vendor/bin/pest --coverage
```

### Local ASP.NET Mock Testing
To test the integration bridge against the local C# ASP mock without a production ERP connection:
```bash
cd asp-local/LocalAspMock
dotnet run
```

---

## 🗂️ Project Structure

```text
blood-group-student-port/
├── asp-local/                    # Standalone .NET Core ASP.NET mock service & tests
│   ├── LocalAspMock/             # C# mock server implementing the ASP ERP API contract
│   └── LocalAspMock.Tests/       # Unit tests for the ASP.NET mock
├── student-admission-portal/     # Core Laravel 11 Application
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/Api/V1/ # REST & Sync Controllers (StudentSyncController, AspSyncController)
│   │   │   └── Middleware/       # EnsureApiKeyIsValid, ApiAuthentication, HMAC verification
│   │   ├── Models/               # Eloquent Models (User, Application, Student, Program, Document)
│   │   └── Services/
│   │       ├── Application/      # Application workflow state machine
│   │       ├── Auth/             # OtpService & token generators
│   │       ├── Integration/      # AspApiService (HTTP client bridge)
│   │       ├── Notifications/    # SMS & Email notification channels
│   │       ├── Payment/          # M-Pesa STK Push & payment processors
│   │       ├── Storage/          # DocumentStorageService
│   │       └── Student/          # AspStudentInformationService (Data proxy)
│   ├── config/                   # Laravel configuration files
│   ├── database/
│   │   ├── migrations/           # Database schema migrations
│   │   └── seeders/              # Academic & test data seeders
│   ├── routes/
│   │   ├── api.php               # ASP.NET sync & student API routes
│   │   ├── web.php               # Portal web UI routes
│   │   └── auth.php              # Authentication routes
│   └── tests/                    # Pest PHP Feature and Unit tests
└── README.md                     # Root documentation
```

---

## 📄 License & Attribution

This project is licensed under the **MIT License**.

Built for academic admissions and ERP integration. Maintained by the engineering team. For support and technical inquiries, please open an issue in the repository.
