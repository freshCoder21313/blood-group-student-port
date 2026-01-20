# System-Level Test Design

## Testability Assessment

- **Controllability**: **PASS**. Laravel framework provides robust tools (Factories, Seeders) for state management. `create_seed_data.php` indicates existing seeding strategy.
- **Observability**: **PASS**. Standard Laravel logging and exception handling. API structure facilitates monitoring.
- **Reliability**: **PASS**. Stateless PHP/Laravel architecture. Transaction management available for data integrity.

## Architecturally Significant Requirements (ASRs)

| Requirement | Risk Score (PxI) | Description |
| :--- | :--- | :--- |
| **PII Encryption** | **9** (3x3) | **CRITICAL**. National ID and Passport Number must be encrypted. `Blind Indexing` required for search. Failure leads to compliance breach. |
| **Peak Load Capacity** | **6** (2x3) | **HIGH**. Support 1,000 concurrent users during deadline week. Performance degradation blocks admissions. |
| **M-Pesa Integration** | **6** (2x3) | **HIGH**. Financial transactions. Failures impact revenue and user trust. Requires idempotency and reconciliation. |
| **ASP Sync Reliability** | **6** (2x3) | **HIGH**. Bi-directional data sync with internal system. Inconsistency causes admin overhead. |

## Test Levels Strategy

- **Unit (60%)**:
    - **Focus**: Business logic in `app/Services`, Custom Validation Rules, PII Encryption/Decryption logic, Job classes.
    - **Rationale**: Fastest feedback, covers complex logic and security constraints cheaply.
- **Integration (Feature) (30%)**:
    - **Focus**: API Endpoints (Sanctum Auth), Database persistence, M-Pesa Callback handling (mocked), ASP Sync Jobs.
    - **Rationale**: Validates wiring between Controller -> Service -> Database and External Service contracts.
- **E2E (10%)**:
    - **Focus**: Critical "Happy Path" (Register -> Apply -> Pay -> Submit), Admin Login -> Sync View.
    - **Rationale**: Ensures the full system works from user perspective. Expensive to run, so kept to critical paths.

## NFR Testing Approach

- **Security (SEC)**:
    - **Unit**: Verify `EncryptedCast` and `BlindIndex` functionality isolated.
    - **Integration**: Verify Sanctum Scopes (`asp:sync`) and Route Protection.
    - **Tooling**: `Pest` for logic, `Larastan` for static analysis.
- **Performance (PERF)**:
    - **Load Testing**: `k6` scripts for Critical User Journeys (Login, Submit Application).
    - **Thresholds**: 95p < 2s for Pages, < 500ms for API.
- **Reliability (REL)**:
    - **Integration**: Test M-Pesa Callback Idempotency (replay same transaction ID).
    - **Integration**: Test ASP Sync Job Retries (simulate 500 errors).
- **Maintainability (TECH)**:
    - **Standard**: `strict_types=1`, Pest Syntax.
    - **CI**: Automated formatting (Pint) and Static Analysis.

## Test Environment Requirements

- **Local**: Laravel Sail (Docker) with MySQL 8.0 and Redis.
- **CI Pipeline**: GitHub Actions with ephemeral MySQL/Redis services.
- **Staging**: Exact replica of Production environment.
- **External Mocks**:
    - **M-Pesa**: Sandbox credentials for Staging; `Http::fake()` for Local/CI.
    - **ASP**: Mock Server based on API Contract for Local/CI.

## Testability Concerns

- **M-Pesa Sandbox Flakiness**: External sandboxes are often unreliable.
    - **Mitigation**: Strictly use `Http::fake()` for all functional tests. Only use Sandbox for manual/E2E verification in Staging.
- **ASP API Availability/Stability**: Integration depends on external system readiness.
    - **Mitigation**: Define strict `OpenAPI` contract. Use Contract Testing or Mock Server to decouple development.

## Recommendations for Sprint 0

1.  **Framework Setup**: Ensure `Pest` is configured with coverage reporting.
2.  **CI Configuration**: Create `.github/workflows/tests.yml` running `pint`, `larastan`, and `pest`.
3.  **Security Helpers**: Implement and test the `BlindIndex` trait immediately as it affects schema design.
4.  **Performance Baseline**: Create a basic `k6` script for the Landing Page to establish a baseline.
