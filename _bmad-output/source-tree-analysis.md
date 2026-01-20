# Source Tree Analysis - Student Admission Portal

## Directory Structure

```
student-admission-portal/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/         # API Controllers (Auth, V1)
│   │   │   └── ...
│   │   ├── Middleware/      # Custom middleware (ApiAuthentication)
│   │   └── Requests/        # Form Requests (Validation)
│   ├── Models/              # Eloquent Models (Student, Application, etc.)
│   ├── Services/            # Business Logic Layer
│   │   ├── Integration/     # ASP System Integration
│   │   ├── Payment/         # M-Pesa Payment Logic
│   │   └── ...
│   └── Providers/           # Service Providers
├── config/                  # Application configuration
├── database/
│   ├── migrations/          # Database schema definitions
│   ├── seeders/             # Data seeders
│   └── factories/           # Model factories for testing
├── public/                  # Web root (index.php)
├── resources/
│   ├── css/                 # Tailwind CSS entry
│   ├── js/                  # JavaScript entry (Vite)
│   └── views/               # Blade templates
├── routes/
│   ├── api.php              # API Routes definition
│   ├── web.php              # Web Routes
│   └── ...
├── storage/                 # Logs, compiled views, file uploads
├── tests/                   # PHPUnit tests
├── composer.json            # PHP Dependencies
├── package.json             # Node/Frontend Dependencies
└── vite.config.js           # Frontend Build Config
```

## Critical Components

- **`app/Http/Controllers/Api`**: Entry point for all API logic. segregated into `Auth` and `V1`.
- **`app/Services/`**: Contains core business logic, separated from controllers. Critical for external integrations (ASP, M-Pesa).
- **`routes/api.php`**: Defines the API contract and routing structure.
- **`database/migrations`**: The source of truth for the data model.
