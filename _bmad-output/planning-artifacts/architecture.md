---
stepsCompleted:
  - 1
  - 2
  - 3
  - 4
  - 5
  - 6
  - 7
  - 8
workflowType: 'architecture'
lastStep: 8
status: 'complete'
completedAt: '2026-01-20'
inputDocuments:
  - _bmad-output/planning-artifacts/prd.md
  - _bmad-output/planning-artifacts/ux-design-specification.md
project_name: 'blood-group-student-port'
user_name: 'Wavister'
date: '2026-01-20'
---

# Architecture Decision Document

_This document builds collaboratively through step-by-step discovery. Sections are appended as we work through each architectural decision together._

## Project Context Analysis

### Requirements Overview

**Functional Requirements:**
The system is a monolithic Laravel web application designed to handle the complete student admission lifecycle.
-   **User-Facing:** 4-step wizard (Personal, Parent, Program, Docs), M-Pesa payment integration (STK Push + Manual Fallback), and Dashboard for status tracking.
-   **System-Facing:** RESTful API endpoints for the ASP to poll pending applications and push status updates.
-   **Admin-Facing:** While primarily handled in ASP, the portal requires logging and potential manual retry mechanisms for failed syncs/payments.

**Non-Functional Requirements:**
-   **Security:** PII must be encrypted. Uploaded files must be stored securely off-public-root. TLS 1.3 for transit.
-   **Reliability:** M-Pesa callbacks must be idempotent. Sync jobs must handle downtime gracefully (queues/retries).
-   **Performance:** 1,000 concurrent users target requires efficient database indexing and caching strategies (likely Redis).
-   **Accessibility:** WCAG 2.1 Level AA compliance dictates semantic HTML and aria-label usage.

**Scale & Complexity:**
-   Primary domain: Web Application (EdTech)
-   Complexity level: Medium
-   Estimated architectural components: 12-15 (Auth, Wizard State, Payment Gateway, Document Manager, Sync Engine, Notification Service, etc.)

### Technical Constraints & Dependencies

-   **Framework:** Laravel 11 (PHP 8.2+).
-   **Frontend:** Blade Templates + TailwindCSS v4 + Vite.
-   **Database:** MySQL (Standard for Laravel).
-   **External Systems:**
    -   Safaricom M-Pesa API (Payment).
    -   Academic Service Platform (ASP) (Internal System of Record).
-   **Infrastructure:** Cron jobs required for sync. Storage required for docs (S3 or local secure).

### Cross-Cutting Concerns Identified

1.  **Security & Privacy:** PII encryption, File security, API Authentication (Sanctum/Keys for ASP).
2.  **Auditability:** Comprehensive logging of all Payment and Sync events.
3.  **State Management:** Robust handling of Application Status transitions.
4.  **Error Handling:** Graceful degradation for 3rd party API failures (M-Pesa, ASP).

## Starter Template Evaluation

### Primary Technology Domain

**Full-Stack Web Application (Laravel)** based on project requirements analysis.

### Starter Options Considered

1.  **Laravel New (Standard):**
    -   *Pros:* Clean slate, maximum control.
    -   *Cons:* Requires manual setup of Authentication, TailwindCSS, and Testing helpers. Slower start.

2.  **Laravel + Jetstream (Livewire/Inertia):**
    -   *Pros:* Powerful features (Teams, 2FA, Profile Photos).
    -   *Cons:* High complexity. Too "heavy" for the stated "minimal JavaScript" and "simple monolithic" requirements.

3.  **Laravel + Breeze (Blade):**
    -   *Pros:* Minimal authentication scaffolding. Uses **Blade Templates** and **TailwindCSS** directly (exact match for requirements). Easy to customize.
    -   *Cons:* Fewer built-in features than Jetstream (but sufficient for this MVP).

### Selected Strategy: Brownfield Modernization

**Rationale for Selection:**
-   **Existing Assets:** The project already contains a mature Laravel structure (`app/Services/Integration`, `app/Services/Payment`) which closely aligns with our desired architecture.
-   **Risk Reduction:** Re-scaffolding would destroy working integration logic.
-   **Goal:** Refactor existing code to strictly enforce the "Service Layer" and "Type Safety" rules defined in `project-context.md`.

**Initialization / Audit Command:**

```bash
# 1. Verify Environment
./vendor/bin/sail up -d

# 2. Dependency Check
./vendor/bin/sail composer install

# 3. Database Migration Status
./vendor/bin/sail artisan migrate:status

# 4. Run Existing Tests to Baseline State
./vendor/bin/sail artisan test
```

**Architectural Decisions Provided by Existing Stack:**

**Language & Runtime:**
-   **PHP 8.2+**: Confirmed in `composer.json`.
-   **Laravel 11**: Confirmed in `composer.json`.

**Styling Solution:**
-   **TailwindCSS**: Verify version in `package.json` (Target v3/v4).
-   **Vite**: Already present in `vite.config.js`.

**Build Tooling:**
-   **Docker (Sail)**: Present.
-   **MySQL**: Configured in `docker-compose.yml`.
-   **Redis**: Configured in `docker-compose.yml`.

**Testing Framework:**
-   **Pest / PHPUnit**: Verify `phpunit.xml` and `tests/` directory structure.

**Code Organization:**
-   **MVC Structure**: Standard.
-   **Services:** `app/Services` already exists. **Action:** Enforce strict usage.

**Note:** The first implementation story is to **audit** the existing codebase against these decisions.

## Core Architectural Decisions

### Decision Priority Analysis

**Critical Decisions (Block Implementation):**
-   Handling of Draft vs. Submitted state in Database.
-   Encryption strategy for PII.
-   Security mechanism for ASP API Sync.
-   Storage driver for sensitive documents.

**Important Decisions (Shape Architecture):**
-   Blind Indexing strategy for searchable encrypted fields.
-   Directory structure for private uploads.

### Data Architecture

**Application State & Drafts:**
-   **Decision:** Use **Nullable Columns** on the main `applications` table.
-   **Rationale:** Standard Laravel pattern. Allows storing partial data (Drafts) easily. Simpler than maintaining a separate "Drafts" table or hydrating JSON.
-   **Implementation:** `status` column enum (`draft`, `submitted`, `approved`, `rejected`) controls validation rules (e.g., fields are only `required` when status transitions to `submitted`).

### Authentication & Security

**PII Protection:**
-   **Decision:** **Laravel Encrypted Casting** + **Blind Indexing**.
-   **Rationale:** Provides strong encryption at rest for sensitive fields (National ID, Passport Number). Blind indexing (storing a hashed version of the ID in a separate column) allows for "Exact Match" lookups (e.g., checking if an applicant already exists) without decrypting the whole table or compromising the raw data.
-   **Version:** Laravel 11 Native Casting.

**ASP API Security:**
-   **Decision:** **Laravel Sanctum (API Tokens)**.
-   **Rationale:** Provides a standardized, secure way to authenticate the ASP system. Allows revoking access if the ASP key is compromised. Supports abilities/scopes (e.g., `asp:sync`) for granular control.

### API & Communication Patterns

-   **Pattern:** **Polling + Push**.
    -   ASP Polls Portal: `GET /api/v1/sync/pending` (Sanctum Protected).
    -   ASP Pushes Status: `POST /api/v1/sync/status` (Sanctum Protected).
-   **Error Handling:** Standard Laravel JSON responses. Failed syncs logged to `daily` log channel.

### Infrastructure & Deployment

**Document Storage:**
-   **Decision:** **Local Private Volume** (MVP) abstraction via `Storage` Facade.
-   **Rationale:** keeps the MVP simple (no AWS costs/setup). Using Laravel's `Storage::disk('private')` allows seamless migration to S3 later by just changing the `.env` driver, without code changes.
-   **Security:** The 'private' disk must be configured to be **outside** the `public/` web root. Files are served via a controlled download route `GET /documents/{id}` that checks policy permissions (Applicant or Admin only).

### Decision Impact Analysis

**Implementation Sequence:**
1.  **Project Audit:** Verify `composer.json` and `docker-compose.yml` (Completed).
2.  **Schema Repair (Brownfield Fixes):**
    -   **Fix Applications Table:** Create migration to make `program_id` and `block_id` nullable to support 'Draft' status (fixes crash in `ApplicationService`).
    -   **Secure PII:** Create migration to change `national_id`/`passport_number` to `text` (for encryption) and add `_hash` columns for blind indexing.
3.  **Auth:** Verify/Configure Sanctum for ASP user.
4.  **Storage:** Configure `private` disk in `filesystems.php`.
5.  **Refactor:** Update `ApplicationService` to handle nullable program IDs and use strict types.
6.  **Feature Dev:** Proceed with Payment and Sync logic.

**Cross-Component Dependencies:**
-   **Draft State:** Validation Logic must be "Status Aware" (loose for drafts, strict for submission).
-   **Encryption:** Search functionality (if needed) depends on the Blind Index column being populated during Create/Update.

## Implementation Patterns & Consistency Rules

### Pattern Categories Defined

**Critical Conflict Points Identified:**
5 areas where AI agents could make different choices (Business Logic, Frontend, Naming, API Format, Error Handling).

### Naming Patterns

**Database Naming Conventions:**
-   **Tables:** Plural, snake_case (e.g., `student_applications`).
-   **Columns:** snake_case (e.g., `student_code`).
-   **Foreign Keys:** `singular_table_id` (e.g., `user_id`).
-   **Indexes:** `idx_table_columns` (e.g., `idx_applications_status`).

**API Naming Conventions:**
-   **Endpoints:** Plural kebab-case (e.g., `/api/v1/student-applications`).
-   **Route Names:** camelCase with dots (e.g., `api.applications.index`).
-   **Internal:** Methods follow HTTP verbs (e.g., `store`, `update`, `destroy`).

**Code Naming Conventions:**
-   **Controllers:** PascalCase + Resource (e.g., `ApplicationController`).
-   **Services:** PascalCase + Domain (e.g., `PaymentService`).
-   **Methods:** camelCase (e.g., `processPayment`).
-   **Tests:** snake_case (e.g., `test_user_can_submit_application`).

### Structure Patterns

**Project Organization:**
-   **Logic:** **Service Classes** (`app/Services`) for domain logic. Controllers should be thin (request validation -> service call -> response).
-   **Frontend:** **Blade Components** (`resources/views/components`) for all reusable UI elements. No raw HTML duplication.
-   **Tests:** Co-located in `tests/Feature` and `tests/Unit` mirroring the app structure where possible.

### Format Patterns

**API Response Formats:**
-   **Standard:** Use **Eloquent API Resources** (`JsonResource`) for all API endpoints.
-   **Structure:** `{ "data": { ...attributes }, "meta": { ... } }`.
-   **Dates:** ISO 8601 Strings.

### Process Patterns

**Error Handling Patterns:**
-   **Strategy:** Throw **Custom Exceptions** in Services (e.g., `PaymentFailedException`).
-   **Catching:** Catch in Controllers.
-   **Response:** Return Flash Messages (`with('error', ...)` for Web) or JSON (`{ "message": ... }` for API).

### Enforcement Guidelines

**All AI Agents MUST:**
1.  Use **Blade Components** for UI elements.
2.  Use **Service Classes** for logic > 10 lines.
3.  Use **API Resources** for all API outputs.
4.  Encrypt PII using **Native Casting**.

**Pattern Examples:**

**Good Example (Controller):**
```php
public function store(StoreApplicationRequest $request, ApplicationService $service)
{
    try {
        $service->create($request->validated());
        return to_route('dashboard')->with('success', 'Application started.');
    } catch (ApplicationException $e) {
        return back()->with('error', $e->getMessage());
    }
}
```

**Anti-Pattern (Avoid):**
```php
public function store(Request $request) {
    // Logic inside controller
    $app = new Application();
    $app->fill($request->all()); // Dangerous mass assignment
    $app->save();
    // Raw blade return
    return view('dashboard', ['app' => $app]);
}
```

## Project Structure & Boundaries

### Complete Project Directory Structure

```text
blood-group-student-port/
├── app/
│   ├── Actions/                    # Single-action classes (if Service gets too big)
│   ├── Console/
│   │   └── Commands/
│   │       ├── SyncPendingApplications.php    # Cron: Poll ASP
│   │       └── ReconcilePayments.php          # Cron: Check M-Pesa
│   ├── Exceptions/
│   │   ├── MpesaTransactionFailed.php
│   │   └── AspSyncFailed.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   └── AspSyncController.php      # ASP Endpoints
│   │   │   ├── Auth/                          # Breeze Controllers
│   │   │   ├── ApplicationController.php      # Wizard Logic
│   │   │   ├── DashboardController.php        # Student Dashboard
│   │   │   ├── DocumentController.php         # Secure Download
│   │   │   └── PaymentController.php          # M-Pesa Callback
│   │   ├── Middleware/
│   │   │   └── EnsureApplicationIsDraft.php   # Edit Guard
│   │   ├── Requests/
│   │   │   ├── StoreApplicationRequest.php
│   │   │   └── UpdateApplicationRequest.php
│   │   └── Resources/
│   │       └── ApplicationResource.php        # API Transformer
│   ├── Models/
│   │   ├── Application.php                    # Main Model
│   │   ├── Document.php
│   │   └── User.php
│   ├── Policies/
│   │   └── ApplicationPolicy.php              # Authz Logic
│   ├── Services/
│   │   ├── ApplicationService.php             # Core Logic
│   │   ├── DocumentService.php                # Upload/S3 Logic
│   │   └── Payment/
│   │       └── MpesaService.php               # M-Pesa Integration
│   └── Providers/
│       └── AppServiceProvider.php
├── config/
│   ├── mpesa.php                              # M-Pesa Creds
│   ├── services.php                           # ASP Creds
│   └── filesystems.php                        # 'private' disk config
├── database/
│   ├── migrations/
│   │   ├── xxxx_create_applications_table.php
│   │   └── xxxx_create_documents_table.php
│   ├── factories/
│   └── seeders/
├── resources/
│   ├── css/
│   │   └── app.css                            # Tailwind
│   ├── js/
│   │   └── app.js                             # Alpine/Vite
│   └── views/
│       ├── components/
│       │   ├── application-logo.blade.php
│       │   ├── input-text.blade.php
│       │   ├── status-badge.blade.php
│       │   └── wizard-step.blade.php
│       ├── layouts/
│       │   ├── app.blade.php
│       │   └── guest.blade.php
│       ├── application/
│       │   ├── create.blade.php               # Wizard Steps
│       │   └── show.blade.php                 # Summary
│       └── dashboard.blade.php
├── routes/
│   ├── api.php                                # ASP Sync Routes
│   ├── web.php                                # Wizard Routes
│   └── auth.php                               # Breeze Routes
├── storage/
│   └── app/
│       └── private/                           # Secure Docs
├── tests/
│   ├── Feature/
│   │   ├── ApplicationSubmissionTest.php
│   │   ├── AspSyncTest.php
│   │   └── PaymentCallbackTest.php
│   └── Unit/
│       └── MpesaServiceTest.php
├── docker-compose.yml                         # Sail Config
├── package.json
├── vite.config.js
└── composer.json
```

### Architectural Boundaries

**API Boundaries:**
-   **ASP Interface:** Defined in `routes/api.php`. Controlled by `AspSyncController`. Protected by `auth:sanctum`.
-   **M-Pesa Interface:** `PaymentController` (Webhook). Protected by IP Whitelist (if possible) or signature verification.

**Component Boundaries:**
-   **Frontend:** Blade Views **only** display data. Logic lives in Controllers/Livewire.
-   **Reusable UI:** All UI elements (buttons, inputs) MUST use `x-components`.
-   **State:** Application state (`draft` vs `submitted`) is enforced by `ApplicationService` and `ApplicationPolicy`.

**Service Boundaries:**
-   **Payment:** `MpesaService` is the **only** class allowed to talk to Safaricom.
-   **Documents:** `DocumentService` is the **only** class allowed to touch the `Storage` facade.

**Data Boundaries:**
-   **PII:** Access to PII columns (`national_id`, `passport`) happens **only** through the `Application` model's accessor methods (which handle decryption).

### Requirements to Structure Mapping

**Feature/Epic Mapping:**
-   **User Wizard:** `ApplicationController` + `resources/views/application/` + `ApplicationService`.
-   **Payment:** `PaymentController` + `MpesaService`.
-   **ASP Sync:** `AspSyncController` + `SyncPendingApplications` (Command) + `ApplicationResource`.

**Cross-Cutting Concerns:**
-   **Auth:** `app/Http/Controllers/Auth` (Breeze).
-   **Security:** `app/Policies` + `app/Http/Middleware`.

### Integration Points

**Internal Communication:**
-   Controllers call Services.
-   Services fire Events (e.g., `ApplicationSubmitted`).
-   Listeners handle side effects (e.g., `SendConfirmationEmail`).

**External Integrations:**
-   **Safaricom:** Outbound (STK Push) via `MpesaService`. Inbound (Callback) via `PaymentController`.
-   **ASP:** Outbound (Push Status) via `SyncPendingApplications`. Inbound (Poll) via `AspSyncController`.

**Data Flow:**
1.  User Input -> Request Validation -> Controller -> Service -> Model -> DB.
2.  DB -> Model -> Resource (Transform) -> JSON Response -> ASP.

### File Organization Patterns

**Configuration Files:**
-   `config/mpesa.php`: Stores Paybill, Passkey, URLs.
-   `.env`: Stores Secrets (Client IDs, Secrets).

**Source Organization:**
-   **Domain Logic:** Grouped by Feature where possible, but sticking to standard Laravel `app/` structure to avoid over-engineering.

**Test Organization:**
-   `tests/Feature`: End-to-end HTTP tests (User logs in -> Fills form -> Submits).
-   `tests/Unit`: Isolated logic tests (M-Pesa signature verification, Service calculations).

### Development Workflow Integration

**Development Server Structure:**
-   **Sail:** `docker-compose.yml` defines the `app`, `mysql`, `redis`, `mailpit` services.
-   **Vite:** Runs inside the container or on host, proxying to Sail.

**Build Process Structure:**
-   `npm run build`: Compiles Tailwind/Alpine to `public/build`.
-   `php artisan optimize`: Caches Config/Routes for production.

**Deployment Structure:**
-   Standard Laravel Deployment:
    1.  `composer install --no-dev`
    2.  `npm ci && npm run build`
    3.  `php artisan migrate --force`
    4.  `php artisan config:cache`

## Architecture Validation Results

### Coherence Validation ✅

**Decision Compatibility:**
-   **Laravel 11 + Breeze:** Native compatibility. No conflicts.
-   **Sail + Redis:** Redis is standard for Laravel Queues/Cache. No driver conflicts.
-   **Sanctum + API:** Sanctum is the default API auth solution. Compatible with the "Poll/Push" sync pattern.

**Pattern Consistency:**
-   **Service Pattern:** Aligns with the Monolithic structure. Prevents Controller bloat.
-   **Blade Components:** Consistent with TailwindCSS requirements.

**Structure Alignment:**
-   The defined `app/Services` and `app/Http/Controllers/Api` structure directly supports the decision to separate "Wizard Logic" from "System Sync Logic".

### Requirements Coverage Validation ✅

**Epic/Feature Coverage:**
-   **Application Wizard:** Covered by `ApplicationController` + `ApplicationService` + Nullable DB Columns.
-   **Payment:** Covered by `MpesaService` + `PaymentController`.
-   **ASP Sync:** Covered by `AspSyncController` + Sanctum Auth + `SyncPendingApplications` Command.

**Functional Requirements Coverage:**
-   **FR-01 (Wizard):** Supported by Routes/Views.
-   **FR-05 (Payment):** Supported by Async Callback structure.
-   **FR-09 (Sync):** Supported by Scheduled Commands.

**Non-Functional Requirements Coverage:**
-   **Security (PII):** Covered by Encrypted Casting + Policy Middleware.
-   **Performance:** Redis Cache + Queue drivers selected.
-   **Compliance:** Private Storage driver ensures docs are not public.

### Implementation Readiness Validation ✅

**Decision Completeness:**
-   All "Critical" decisions (DB State, Auth, Sync, Storage) are made and versioned.

**Structure Completeness:**
-   Full file tree provided, including specific Service and Controller names.

**Pattern Completeness:**
-   Naming, Formatting, and Error Handling patterns are explicitly defined with examples.

### Architecture Readiness Assessment

**Overall Status:** READY FOR IMPLEMENTATION
**Confidence Level:** High

**Key Strengths:**
1.  **Simplicity:** Leverages standard Laravel features (Sanctum, Breeze, Sail) without over-engineering.
2.  **Security:** PII encryption and Private Storage are baked into the core design.
3.  **Scalability:** Async Jobs for Payment/Sync prevent bottlenecks.

### Implementation Handoff

**AI Agent Guidelines:**
-   **Strictly follow** the `app/Services` pattern for business logic.
-   **Always** use `x-blade-components` for UI.
-   **Never** expose PII in API resources without explicit transformation.

**First Implementation Priority:**
Run the Sail initialization command to scaffold the environment.

## Architecture Completion Summary

### Workflow Completion

**Architecture Decision Workflow:** COMPLETED ✅
**Total Steps Completed:** 8
**Date Completed:** 2026-01-20
**Document Location:** _bmad-output/planning-artifacts/architecture.md

### Final Architecture Deliverables

**📋 Complete Architecture Document**

-   All architectural decisions documented with specific versions
-   Implementation patterns ensuring AI agent consistency
-   Complete project structure with all files and directories
-   Requirements to architecture mapping
-   Validation confirming coherence and completeness

**🏗️ Implementation Ready Foundation**

-   13 architectural decisions made
-   6 implementation patterns defined
-   5 architectural components specified
-   3 requirement categories fully supported

**📚 AI Agent Implementation Guide**

-   Technology stack with verified versions
-   Consistency rules that prevent implementation conflicts
-   Project structure with clear boundaries
-   Integration patterns and communication standards

### Implementation Handoff

**For AI Agents:**
This architecture document is your complete guide for implementing blood-group-student-port. Follow all decisions, patterns, and structures exactly as documented.

**First Implementation Priority:**
Audit and Refactor existing codebase to align with architectural decisions.

**Development Sequence:**

1.  **Audit:** Analyze existing `app/Services` and `routes/api.php` against the architecture.
2.  **Environment:** Ensure Sail/Docker environment is running and dependencies are up to date.
3.  **Refactor:** Align existing Service classes with strict type rules.
4.  **Extend:** Build missing features on top of the stabilized foundation.
5.  **Maintain:** Enforce consistency with `project-context.md`.

### Quality Assurance Checklist

**✅ Architecture Coherence**

-   [x] All decisions work together without conflicts
-   [x] Technology choices are compatible
-   [x] Patterns support the architectural decisions
-   [x] Structure aligns with all choices

**✅ Requirements Coverage**

-   [x] All functional requirements are supported
-   [x] All non-functional requirements are addressed
-   [x] Cross-cutting concerns are handled
-   [x] Integration points are defined

**✅ Implementation Readiness**

-   [x] Decisions are specific and actionable
-   [x] Patterns prevent agent conflicts
-   [x] Structure is complete and unambiguous
-   [x] Examples are provided for clarity

### Project Success Factors

**🎯 Clear Decision Framework**
Every technology choice was made collaboratively with clear rationale, ensuring all stakeholders understand the architectural direction.

**🔧 Consistency Guarantee**
Implementation patterns and rules ensure that multiple AI agents will produce compatible, consistent code that works together seamlessly.

**📋 Complete Coverage**
All project requirements are architecturally supported, with clear mapping from business needs to technical implementation.

**🏗️ Solid Foundation**
The chosen starter template and architectural patterns provide a production-ready foundation following current best practices.

---

**Architecture Status:** READY FOR IMPLEMENTATION ✅

**Next Phase:** Begin implementation using the architectural decisions and patterns documented herein.

**Document Maintenance:** Update this architecture when major technical decisions are made during implementation.
