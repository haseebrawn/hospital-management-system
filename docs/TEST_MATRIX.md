# Test Matrix (Phase-wise)

This file maps **frontend phases/modules** to the current **Feature tests**.

Run all feature tests:

`php artisan test --testsuite=Feature`

## Phase 1 — Auth + foundation

- Web login throttling: `tests/Feature/Web/AuthThrottleTest.php`
- Base homepage redirect: `tests/Feature/ExampleTest.php`

## Phase 2 — Patients + Appointments

- Patients web UI: `tests/Feature/Web/PatientsWebTest.php`
  - patient history page
- Appointments web UI: `tests/Feature/Web/AppointmentsWebTest.php`
  - patient history shortcut
  - care workflow panel
- Medical records web UI: `tests/Feature/Web/MedicalRecordsWebTest.php`
  - appointment workflow prefill
  - linked appointment context

## Phase 3 — Lab + Pharmacy + Billing

- Lab tests web UI: `tests/Feature/Web/LabTestsWebTest.php`
  - appointment workflow prefill
- Medicines web UI: `tests/Feature/Web/MedicinesWebTest.php`
- Prescriptions web UI: `tests/Feature/Web/PrescriptionsWebTest.php`
  - appointment workflow prefill
- Billing web UI: `tests/Feature/Web/BillingWebTest.php`
  - invoice creation
  - appointment workflow prefill
  - partial payment recording
  - full payment quick action
  - printable receipt page

## Phase 4 — HR + Shifts + Wards/Beds

- Staff web UI: `tests/Feature/Web/StaffWebTest.php`
- Shifts web UI: `tests/Feature/Web/ShiftsWebTest.php`
- Wards/Beds/Allocations web UI: `tests/Feature/Web/WardsBedsWebTest.php`

## Phase 5 — Admin + Reports + System

- Admin users management: `tests/Feature/Web/AdminUsersWebTest.php`
- Reports pages and exports: `tests/Feature/Web/ReportsWebTest.php`
  - report page access
  - CSV export
  - department filter scoping
- System tools (backups/logs): `tests/Feature/Web/SystemWebTest.php`

## Phase 6 — Production Readiness

- System backup automation: `tests/Feature/Web/SystemWebTest.php`
- Queueable notification contract: `tests/Unit/HospitalSystemNotificationTest.php`
- Report exports and filters: `tests/Feature/Web/ReportsWebTest.php`
