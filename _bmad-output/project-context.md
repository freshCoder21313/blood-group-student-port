---
project_name: 'blood-group-student-port'
user_name: 'Wavister'
date: '2026-01-20'
sections_completed: ['technology_stack', 'critical_rules', 'usage_guidelines']
status: 'complete'
rule_count: 15
optimized_for_llm: true
---

# Project Context for AI Agents

_This file contains critical rules and patterns that AI agents must follow when implementing code in this project. Focus on unobvious details that agents might otherwise miss._

---

## Technology Stack & Versions

- **PHP:** 8.2+
- **Laravel:** 11.x
- **Stack:** Blade + TailwindCSS v4 + Alpine.js
- **Auth:** Laravel Breeze (Blade) + Sanctum (API)
- **Database:** MySQL 8.0
- **Cache/Queue:** Redis

## Critical Implementation Rules

### Language & Framework Rules
- **Strict Types:** `declare(strict_types=1);` required in all PHP files.
- **Service Pattern:** Business logic MUST reside in `app/Services/`, not Controllers.
- **Validation:** Use `FormRequest` classes for all POST/PUT requests.
- **UI Components:** MUST use Breeze Blade components (`<x-text-input>`, `<x-primary-button>`) for consistent styling.

### Testing Rules
- **Framework:** Use **Pest** syntax (`it('...', function () {})`).
- **Location:** Feature tests in `tests/Feature`, Unit tests in `tests/Unit`.
- **Coverage:** Test both Happy Path and Validation Failures.

### Security & Data Rules
- **PII:** `national_id` and `passport_number` columns are encrypted (TEXT).
- **Searching:** NEVER query PII columns directly. ALWAYS query the `_index` column (Blind Index) using a hashed value.
- **API Output:** ALWAYS use `JsonResource` classes. NEVER return raw Models to prevent PII leaks.
- **Drafts:** Database columns are nullable to support "Draft" status. Validation is stricter for "Submitted" status.
- **Storage:** Sensitive docs go to `Storage::disk('private')`.

---

## Usage Guidelines

**For AI Agents:**

- Read this file before implementing any code.
- Follow ALL rules exactly as documented.
- When in doubt, prefer the more restrictive option (e.g., stricter types, higher security).
- Update this file if new patterns emerge.

**For Humans:**

- Keep this file lean and focused on agent needs.
- Update when technology stack changes.
- Review quarterly for outdated rules.

Last Updated: 2026-01-20
