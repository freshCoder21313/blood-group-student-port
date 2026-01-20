# Story 1.1: Install & Configure Breeze (Blade)

Status: ready-for-dev

<!-- Note: Validation is optional. Run validate-create-story for quality check before dev-story. -->

## Story

As a System Admin,
I want the Laravel Breeze (Blade) stack installed,
so that I have the standard Login/Register UI views and controllers.

## Acceptance Criteria

1.  **Given** the existing `routes/auth.php` contains API routes
    **When** I install Breeze
    **Then** the existing API routes are preserved (renamed to `routes/api_auth.php`)
    **And** `routes/auth.php` is replaced with standard Breeze Web routes
    **And** `resources/views/auth/*.blade.php` files exist and render correctly

## Tasks / Subtasks

- [ ] **Audit & Protect Existing Routes**
    - [ ] Rename `routes/auth.php` to `routes/api_auth.php`.
    - [ ] Register `routes/api_auth.php` in `routes/api.php` or `bootstrap/app.php` to ensure API endpoints remain accessible.
- [ ] **Install Breeze**
    - [ ] Run `composer require laravel/breeze --dev`.
    - [ ] Run `php artisan breeze:install blade` (Select 'Blade' stack, 'No' to dark mode, 'Pest' for tests).
- [ ] **Verify Installation**
    - [ ] Check `routes/auth.php` contains standard Breeze routes.
    - [ ] Check `resources/views/auth/` contains Blade files.
    - [ ] Verify `vite.config.js` uses Laravel plugin.
- [ ] **Frontend Build**
    - [ ] Run `npm install`.
    - [ ] Run `npm run build`.
- [ ] **Testing**
    - [ ] Run `php artisan test` to ensure no regressions.
    - [ ] Manually verify `/login` and `/register`.

## Dev Notes

### 🚨 Critical Brownfield Warning
**Do NOT assume a fresh install.** This project has existing API routes and services.
-   The existing `routes/auth.php` contains critical API authentication logic. **You must preserve this.**
-   The architecture mandates a **Brownfield Modernization** approach.

### Architecture Compliance
-   **Service Pattern:** Keep business logic in `app/Services`.
-   **UI Components:** Use Blade Components (`<x-input>`, etc.) provided by Breeze.
-   **Styling:** Use TailwindCSS v4 classes.

### Project Structure Notes
-   **Controllers:** Breeze controllers go in `app/Http/Controllers/Auth`.
-   **Views:** Breeze views go in `resources/views/auth`.

### References
-   [Source: _bmad-output/planning-artifacts/architecture.md#Starter Template Evaluation]
-   [Source: _bmad-output/project-context.md]

## Dev Agent Record

### Agent Model Used
{{agent_model_name_version}}

### Debug Log References

### Completion Notes List

### File List
