**🔥 CODE REVIEW FINDINGS, Wavister!**

**Story:** 4-2-pending-applications-endpoint.md
**Git vs Story Discrepancies:** 0 found (Files match claims)
**Issues Found:** 2 High, 2 Medium, 1 Low

## 🔴 CRITICAL ISSUES
- **Status Mismatch with Architecture/Requirements:**
    - Story AC #3 requires: "receive a JSON list of applications with status 'submitted'".
    - Code returns status `pending_approval` (from DB).
    - Architecture Document explicitly defines status enum as `draft`, `submitted`, `approved`...
    - **Risk:** ASP Integration failure due to unexpected status string.
- **Performance/DoS Risk (Missing Pagination):**
    - `AspSyncController::pending` uses `->get()` on the applications table.
    - **Risk:** If 5000 applications are pending, this will OOM the server or timeout. Must use `cursorPaginate` or `chunking` for sync endpoints.

## 🟡 MEDIUM ISSUES
- **Useless Data in DocumentResource:**
    - `DocumentResource` returns internal storage `path` (e.g., `docs/passport.pdf`).
    - ASP cannot access this path directly (it's on a private disk).
    - **Fix:** Should return a `download_url` pointing to `GET /api/v1/documents/{id}/download`.
- **Inefficient Querying (Over-fetching):**
    - `AspSyncController` eager loads `program` and `payment` (`->with(['student', 'program', 'documents', 'payment'])`).
    - `ApplicationResource` **does not use** `program` (only `program_id`) or `payment` data.
    - **Fix:** Remove unused relationships from eager load to save DB resources.

## 🟢 LOW ISSUES
- **Test Quality / Technical Debt:**
    - `tests/Unit/Resources/ResourceTest.php` manually creates `Document` records because "Document factory is missing".
    - Should verify if `DocumentFactory` exists and use it, or create it.
