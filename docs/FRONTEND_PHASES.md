# Hospital Management System (Laravel) — Frontend (Blade) Phase Plan

This repo already contains a working API (routes in `routes/api.php`) and a starter Blade UI (auth screens + layout + dashboard skeleton).

Goal: build a complete **web (Blade) admin/staff UI** on top of the existing models/controllers, **phase-wise**, without breaking the API.

## Current backend inventory (from `routes/api.php`)

API prefix: `/api/v1`

- Auth: register, login, profile, logout
- Notifications: list, mark-all-read
- Admin (RBAC): users list + assign/remove role + update department
- Patients: CRUD
- Appointments: CRUD (+ role-based access)
- Lab tests: CRUD
- Pharmacy: medicines CRUD + pending prescriptions + dispense
- Billing: invoices list/show/create + pay/cancel
- HR: staff CRUD
- Shifts: list + assign
- Wards/Beds/Bed allocation
- Reports: patients/appointments/billing/pharmacy/lab/ward-bed/staff
- System admin: backups + audit logs

## Important architecture decision (web UI + roles)

This project uses Spatie Roles/Permissions with `guard_name = api` (see `config/permission.php` and `app/Models/User.php`).

For the Blade UI we will:

1) Keep **session auth** for web routes (`auth` middleware).
2) Continue using **Spatie roles** stored under `guard_name = api` for now.
3) Use `role:` / `permission:` middleware on web routes normally (session auth), relying on `app/Models/User.php` (`$guard_name = 'api'`) so role checks resolve against the existing role records.

This avoids a risky migration of role guards during UI development.

## Phase 0 — Requirements + UI blueprint (Docs only)

Deliverables:

- Route map for web UI (page URLs)
- Navigation (sidebar) map per role
- Decide UI layout rules: tables, forms, empty states, flash messages, pagination
- Decide which pages call Eloquent directly vs. call internal service classes

Web page list (initial):

- Dashboard: metrics + recent activity
- Patients: index/create/edit/show
- Appointments: index/create/edit/show
- Lab: index/create/edit/show
- Pharmacy: medicines index/create/edit + pending prescriptions
- Billing: invoices index/show + create + pay/cancel
- HR: staff index/create/edit
- Shifts: index + assign
- Wards/Beds: index/create/edit
- Reports: filters + export (later)
- Admin: users list + role assignment + department assignment
- System: backups + activity logs + login logs (super admin/admin only)

## Phase 1 — Web foundation (Auth + layout + navigation)

Deliverables:

- Web routes grouped by module with correct middleware
- Replace temporary dashboard view route with `DashboardController@index`
- Shared Blade components:
  - `layouts/app.blade.php` stays as main shell
  - `partials/flash.blade.php` for success/error messages
  - `partials/empty.blade.php` for empty states
- Sidebar links become real routes (no `href="#"`)

Acceptance:

- User can log in (web session) and open `/dashboard`
- Sidebar navigation works and highlights active route

## Phase 2 — Patients + Appointments UI

Deliverables:

- Patients pages:
  - Table + search + pagination
  - Create/edit form with validation errors
- Appointments pages:
  - Table + status filter
  - Create/edit form

Acceptance:

- CRUD works end-to-end via Eloquent (web controllers), respecting role access

Status:

- Implemented: Patients CRUD (search + pagination) + Appointments CRUD (search + status filter + pagination)
- Verification: Feature tests added for Patients/Appointments web flows

## Phase 3 — Lab + Pharmacy + Billing UI

Deliverables:

- Lab tests CRUD pages
- Medicines CRUD + dispense flow
- Billing invoice flow (create/show/pay/cancel)

Acceptance:

- Workflows match existing API behavior and business rules

Status:

- Implemented: Lab Tests CRUD + Medicines CRUD (search + status filter + pagination)
- Implemented: Billing invoices UI (list/search/filter, create with items, show, pay/cancel)

## Phase 4 — HR + Shifts + Wards/Beds UI

Deliverables:

- Staff CRUD + role-based permissions
- Shift assignment page
- Wards + Beds + Allocations pages

Acceptance:

- Department admin constraints enforced where applicable

Status:

- Implemented: Staff CRUD (web)
- Implemented: Shift assignment + listing (web)
- Implemented: Wards CRUD, Beds CRUD, Bed allocations (assign/release/transfer) (web)
- Verification: Feature tests added for Staff/Shifts/Wards-Beds web flows

## Phase 5 — Admin dashboard + Reports + System tools

Deliverables:

- Admin user management pages (roles + departments)
- Reports pages (filters + export later)
- Backups + audit logs UI (super admin/admin)

Acceptance:

- All modules visible and usable according to roles

Status:

- Implemented: Admin users management (assign/remove role, update department)
- Implemented: Reports pages (patients, appointments, billing, ward-bed, staff)
- Implemented: System tools UI (backups, activity logs, login logs)
- Verification: Feature tests added for Phase 5 pages/actions

## Phase 6 — Hardening

Deliverables:

- Consistent validation + error handling
- Rate limiting for login (already on API; add to web if needed)
- Tests for the web controllers (feature tests)
- Optional: extract shared business logic into service classes

Status:

- Implemented: Web login rate limiting (`throttle:10,1`)
- Implemented: Stricter validation for admin role assignment (role must exist for `guard_name=api`)
- Verification: Added web login throttle feature test
