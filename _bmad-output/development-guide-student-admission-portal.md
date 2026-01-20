# Development Guide - Student Admission Portal

## Prerequisites
- **PHP:** v8.2 or higher
- **Composer:** Latest version
- **Node.js:** v18+ (for Vite/Tailwind)
- **Database:** SQLite (default for dev) or MySQL/PostgreSQL

## Installation (Docker/Sail)

1. **Start Environment**
   ```bash
   ./vendor/bin/sail up -d
   ```

2. **Install Dependencies**
   ```bash
   ./vendor/bin/sail composer install
   ./vendor/bin/sail npm install
   ```

3. **Database Setup**
   ```bash
   ./vendor/bin/sail artisan migrate --seed
   ```

## Security Guidelines

**Handling PII (National ID / Passport)**
- **Encryption:** These fields are encrypted at rest. Use the standard `$student->national_id` accessor to read (auto-decrypts).
- **Searching:** You CANNOT run `where('national_id', $val)`. Instead, use the blind index:
  ```php
  // How to search:
  $hash = hash_hmac('sha256', $value, config('app.key'));
  $student = Student::where('national_id_index', $hash)->first();
  ```

## Running the Application

**Development Server (Concurrent)**
The project uses `concurrently` to run PHP and Vite servers together:
```bash
npm run dev
```
This starts:
- `php artisan serve` (Laravel Backend)
- `php artisan queue:listen` (Queue Worker)
- `vite` (Frontend Assets)

## Testing
Run the PHPUnit test suite:
```bash
php artisan test
```

## Build for Production
```bash
npm run build
php artisan optimize
```

## Configuration
- **M-Pesa:** Configure `MPESA_CONSUMER_KEY`, `MPESA_SECRET`, etc. in `.env`.
- **ASP Integration:** Configure `ASP_API_URL`, `ASP_API_KEY`, `ASP_API_SECRET`.
