# MASTER SYSTEM ARCHITECTURE & INTEGRATION BLUEPRINT
## Student Admission & Academic Portal (Laravel 11 & PHP 8.2+)

This unified master document consolidates the complete architecture, database design, API specification, modular payment structures, and deployment protocols for the Student Admission Portal. It bridges the gap between the offline ASP.NET registrar software and the online Laravel portal.

---

## 🗺️ 1. END-TO-END WORKFLOW LIFECYCLE

The system manages the lifecycle of an applicant from register to fully enrolled student through three key phases:

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                          STUDENT LIFECYCLE WORKFLOW                         │
│                          (Cache Sync & Modular Payments)                    │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│   PHASE 1: REGISTRATION & SUBMISSION (PHP Portal)                           │
│   ══════════════════════════════════                                        │
│   [Register (Email/Phone)] ──► [Verify OTP] ──► [Fill Step-Form (1-4)]      │
│                                                       │                     │
│                                                       ▼                     │
│   [Submit Application] ◄── [Verify Payment] ◄── [Modular Processors]        │
│                                                   ├── Manual Verification   │
│                                                   └── M-Pesa STK Push       │
│                                                                             │
│   PHASE 2: REVIEW & DATA SYNCHRONIZATION                                    │
│   ══════════════════════════════════════                                    │
│   ┌────────────────────┐          GET /api/v1/students?status=pending       │
│   │                    │ ◄───────────────────────────────────────────────   │
│   │   ASP.NET System   │                                                    │
│   │   (LAN Internal)   │ ───────────────────────────────────────────────►   │
│   │                    │     POST /api/v1/students/{code}/academic-records  │
│   └────────────────────┘     (HMAC SHA256 Signature + JSON Payload)         │
│                                                                             │
│   PHASE 3: POST-ADMISSION STUDENT PORTAL (Dashboard)                        │
│   ══════════════════════════════════════                                    │
│   [Auth Redirect] ──► [Hide Application Wizard] ──► [Display Student Portal]│
│                                                                             │
│   * Pulls from cached local database records (grades, schedule, fees)        │
│     using DatabaseStudentInformationService instead of making slow API calls│
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Phase 1: Registration & Submission (Student-facing Portal)
1. **User Sign Up**: Students sign up using email or phone number.
2. **OTP Verification**: The system sends a one-time OTP to prevent spam. Once verified, the student creates a password.
3. **Multi-Step Form**: The registration wizard collects:
   * **Step 1**: Personal Information.
   * **Step 2**: Guardian/Parent Information.
   * **Step 3**: Academic Course Block selection.
   * **Step 4**: Upload of supporting documents (diplomas, medical records).
4. **Billing & Modular Payment**:
   * Displays the payment interface.
   * **Manual Mode**: User uploads receipt image and inputs their transaction code.
   * **M-Pesa Mode**: Automatic STK push trigger and payment verification.

### Phase 2: Review & Integration Sync (PHP Portal ↔ ASP.NET System)
1. **Pull Pending Applications**: The ASP.NET system on the local area network polls the Laravel public server (`GET /api/v1/students?status=pending`).
2. **Review & Assessment**: Registrar staff verifies documents and matches the transaction code.
3. **Approval Status Push**:
   * **If Approved**: ASP calls `POST /api/v1/update-status` (setting status to `Approved` and assigning a generated `student_code`).
   * **If Request Info**: Set status to `request_info` with a review note. Laravel opens the wizard back up for the applicant.

### Phase 3: Active Student Portal (High Availability)
1. **RBAC Switching**: Upon next login, `DashboardController` identifies the user as an `Approved` student and replaces the Admission Wizard with the Student Dashboard.
2. **Local Caching Sync**: To prevent slow real-time intranet requests, the ASP.NET system actively pushes academic updates (`POST /api/v1/students/{student_code}/academic-records`) carrying secure HMAC signatures.
3. **Cached View Rendering**: The Grades, Class Schedule, and Fee Ledger views consume this local MySQL cache instantly.

---

## 🏛️ 2. SYSTEM ARCHITECTURE & NETWORK MODEL

```
                                    ┌─────────────────┐
                                    │    INTERNET     │
                                    └────────┬────────┘
                                             │
                                    ┌────────▼────────┐
                                    │   CLOUDFLARE    │
                                    │   (CDN + WAF)   │
                                    └────────┬────────┘
                                             │
                               ┌──────────────┼──────────────┐
                               │              │              │
                     ┌─────────▼─────┐ ┌──────▼─────┐ ┌──────▼─────┐
                     │  WEB SERVER   │ │ API SERVER │ │ FILE SERVER│
                     │  (Frontend)   │ │ (Backend)  │ │ (Storage)  │
                     │  PHP + Blade  │ │ Laravel 11 │ │ S3/Sftp    │
                     └───────┬───────┘ └──────┬─────┘ └──────┬─────┘
                             │                │              │
                             └────────────────┼──────────────┘
                                              │
                     ┌────────────────────────┼────────────────────────┐
                     │                        │                        │
            ┌────────▼────────┐    ┌──────────▼──────────┐    ┌───────▼───────┐
            │   MySQL DB      │    │   REDIS CACHE       │    │  QUEUE WORKER │
            │   (Primary Store)│   │   (Session/Jobs)    │    │  (Mail/Jobs)  │
            └─────────────────┘    └─────────────────────┘    └───────────────┘
                                              │
                                              │ SECURE VPN / HTTPS HMAC SYNC
                                              │
                               ┌──────────────▼──────────────┐
                               │      ASP.NET SYSTEM         │
                               │      (LAN Internal)         │
                               │  ┌─────────────────────┐    │
                               │  │  SQL Server DB      │    │
                               │  └─────────────────────┘    │
                               └─────────────────────────────┘
```

---

## 🗂️ 3. DATABASE SCHEMA & STRUCTURES

### 3.1 Bảng `users` (Học sinh & Nhân viên)
```sql
CREATE TABLE users (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(50) UNIQUE NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(30) DEFAULT 'student', -- admin, student, applicant
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### 3.2 Bảng `applications` (Hồ sơ tuyển sinh)
```sql
CREATE TABLE applications (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    dob DATE NOT NULL,
    gender VARCHAR(10) NOT NULL,
    national_id VARCHAR(255) NOT NULL, -- Encrypted
    national_id_index VARCHAR(255) NOT NULL, -- Blind Index for search
    parent_name VARCHAR(255) NOT NULL,
    parent_phone VARCHAR(50) NOT NULL,
    parent_email VARCHAR(255) NULL,
    program_id BIGINT NOT NULL,
    status VARCHAR(30) DEFAULT 'draft', -- draft, submitted, pending_verification, approved, rejected, request_info
    student_code VARCHAR(50) UNIQUE NULL,
    review_notes TEXT NULL,
    submitted_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### 3.3 Bảng `payments` (Lịch sử thanh toán tuyển sinh)
```sql
CREATE TABLE payments (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    application_id BIGINT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    transaction_code VARCHAR(100) UNIQUE NOT NULL,
    status VARCHAR(30) DEFAULT 'pending', -- pending, completed, failed
    proof_document_path VARCHAR(255) NULL,
    manual_submission TINYINT(1) DEFAULT 1,
    verified_at TIMESTAMP NULL,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
);
```

### 3.4 Bảng `student_academic_records` (Bộ đệm dữ liệu học tập)
```sql
CREATE TABLE student_academic_records (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    student_code VARCHAR(50) UNIQUE NOT NULL,
    grades JSON NOT NULL,      -- Array of [{code, name, grade}]
    schedule JSON NOT NULL,    -- Array of [{day, time, course, venue}]
    fees JSON NOT NULL,        -- Object containing {balance, currency, status, invoice_history}
    last_synced_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_student_code (student_code)
);
```

---

## 🛠️ 4. MODULAR PAYMENT PROCESSORS

Using a polymorphic architectural pattern, the system decouples manual cash/receipt submissions from mobile payment integration:

### 4.1 Interface Contract
```php
namespace App\Services\Payment;

use App\Models\Application;

interface PaymentProcessorInterface
{
    /**
     * Process payment initiation or verification.
     */
    public function process(Application $application, array $data): bool;
}
```

### 4.2 Manual Payment Processor
Handles uploading receipts to secure S3 disk folders, storing audit logs, and advancing application status to verification queue:
```php
namespace App\Services\Payment;

use App\Models\Application;
use App\Models\Payment;
use Illuminate\Support\Facades\Storage;

class ManualPaymentProcessor implements PaymentProcessorInterface
{
    public function process(Application $application, array $data): bool
    {
        $proofFile = $data['proof_document'];
        $filePath = $proofFile->store('proofs', 'public');

        Payment::create([
            'application_id' => $application->id,
            'amount' => $data['amount'],
            'transaction_code' => strtoupper($data['transaction_code']),
            'proof_document_path' => $filePath,
            'status' => 'pending',
            'manual_submission' => true,
        ]);

        $application->update(['status' => 'pending_verification']);

        return true;
    }
}
```

---

## 🛡️ 5. SECURITY & SYNC API IMPLEMENTATION

To guarantee data integrity between the ASP.NET local host and Laravel, the sync API uses dynamic HMAC-SHA256 signature verification.

### 5.1 Push API Signature Calculation
Before calling the API, the ASP.NET system constructs:
$$\text{Signature} = \text{HMAC-SHA256}(\text{RequestBody} + \text{Timestamp}, \text{SecretKey})$$

Laravel validates this using a dedicated middleware:

```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthentication
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key');
        $timestamp = $request->header('X-Timestamp');
        $signature = $request->header('X-Signature');

        if (!$apiKey || !$timestamp || !$signature) {
            return response()->json(['error' => 'Unauthorized: Missing headers'], 401);
        }

        if ($apiKey !== config('services.asp.api_key')) {
            return response()->json(['error' => 'Unauthorized: Invalid API Key'], 401);
        }

        // Prevent Replay Attacks (Max 5 minutes difference)
        if (abs(time() - (int)$timestamp) > 300) {
            return response()->json(['error' => 'Request expired'], 401);
        }

        // Validate HMAC signature
        $body = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $body . $timestamp, config('services.asp.api_secret'));

        if (!hash_equals($expectedSignature, $signature)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        return $next($request);
    }
}
```

---

## 🧪 6. TESTING & VALIDATION WORKFLOW

### 6.1 Local Test Environment Setup (SQLite / MySQL)
To run local test suites, you can dynamically configure `.env.testing` to run in-memory SQLite:
```ini
APP_ENV=testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
CACHE_STORE=array
MAIL_MAILER=array
```

If testing JSON querying and casting strictly matching MySQL, spin up an isolated MySQL container:
```bash
docker run --name portal-mysql -p 3306:3306 -e MYSQL_DATABASE=student_admission_test -e MYSQL_ROOT_PASSWORD=password -d mysql:8.0
```

### 6.2 Test Command Executions
* **Run Sync API tests**:
  ```bash
  ./vendor/bin/pest tests/Feature/StudentAcademicRecordsSyncTest.php
  ```
* **Run Payments tests**:
  ```bash
  ./vendor/bin/pest tests/Feature/PaymentCallbackTest.php
  ```
* **Run entire test suite**:
  ```bash
  ./vendor/bin/pest
  ```

---

## 🌐 7. HOSTINGER PRODUCTION DEPLOYMENT BLUEPRINT

### 7.1 Production Configuration
Deploying to Hostinger SMTP requires specific configurations inside `.env`:
```ini
APP_ENV=production
APP_DEBUG=false

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=hostinger_db_name
DB_USERNAME=hostinger_db_user
DB_PASSWORD=hostinger_db_pass

# Pull from ASP Database Cache
STUDENT_INFO_DRIVER=database

# SMTP Config for hostinger.com
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=notifications@yourdomain.com
MAIL_PASSWORD=your_secure_password
MAIL_ENCRYPTION=ssl
```

### 7.2 Post-Deployment Optimization Script
Run the following optimization commands in your Hostinger SSH shell upon deploying a new release:
```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
```
