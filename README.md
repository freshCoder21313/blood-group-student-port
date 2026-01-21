# Student Admission Portal (PHP Web Service)

Online student admission portal system, integrated with the internal ASP.NET system. This project provides RESTful APIs for student enrollment, application submission, and two-way data synchronization with the training management system (ASP System).

## 🚀 System Requirements

Ensure your machine has the following tools installed:

*   **PHP**: >= 8.2 (8.3 Recommended)
*   **Composer**: Dependency manager for PHP.
*   **Database**: MySQL 8.0+ or MariaDB 10.11+.
*   **Web Server**: Nginx/Apache or use PHP built-in server.
*   **Extensions**: `bcmath`, `ctype`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, `curl`.

## 📦 Installation & Configuration

Follow these steps to set up the project in your local environment:

### 1. Clone and Install Dependencies

Navigate to the project directory and install PHP libraries:

```bash
cd student-admission-portal
composer install
```

### 2. Environment Configuration (.env)

Copy the example configuration file and generate the application key:

```bash
cp .env.example .env
php artisan key:generate
```

Open the `.env` file and configure the Database information:

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=student_admission
DB_USERNAME=root
DB_PASSWORD=your_password
```

Configure ASP System integration (If needing to test internal APIs):

```ini
ASP_API_BASE_URL=https://internal-asp.school.local/api
ASP_API_KEY=your_test_key
ASP_API_SECRET=your_test_secret
```

### 3. Initialize Database

Run migrations to create tables in the database (Users, Applications, Students, etc.):

```bash
php artisan migrate
```

### 4. Seed Sample Data (Optional)

If you want sample data for testing (Academic Programs, Admission Blocks):

```bash
php artisan db:seed
```

## 🛠️ Running the Application

Start the local development server:

```bash
php artisan serve
```

The application will run at: `http://localhost:8000`

## 🔌 API Documentation

The system provides the following main API groups:

### 1. Authentication (Public)
*   `POST /api/register`: Register a new account.
*   `POST /api/login`: Login to get Token.
*   `POST /api/verify-otp`: Verify OTP.

### 2. Internal Sync API (For ASP System)
*Requires Headers:* `X-API-Key`, `X-Timestamp`, `X-Signature`

*   `GET /api/v1/students`: Get list of applications (Filter by status, date).
*   `GET /api/v1/students/{id}`: Get application details.
*   `POST /api/v1/update-status`: Update application status (Approved/Rejected).
*   `POST /api/v1/bulk-update-status`: Bulk update.

### 3. Student Data (Proxy to ASP)
*   `GET /api/v1/students/{code}/grades`: Look up grades.
*   `GET /api/v1/students/{code}/fees`: Look up fees.

## 📂 Main Project Structure

*   `app/Models`: Contains Entities (User, Student, Application...).
*   `app/Http/Controllers/Api/V1`: Controllers handling main API logic.
*   `app/Services/Integration`: Services communicating with ASP System.
*   `app/Http/Middleware/ApiAuthentication.php`: Security middleware for HMAC authentication for internal API.
*   `database/migrations`: Database structure definitions.

## 🧪 Testing

Run Unit Tests and Feature Tests:

```bash
php artisan test
```

---
**Note:** This project uses Laravel 11.x. Please refer to [Laravel Documentation](https://laravel.com/docs) for more details about the Framework.
