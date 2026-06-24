# Hospital Management System — Project Audit & Next Steps

Date: 2026-06-11

This document is a full audit of the current Hospital Management System project. It explains what is already implemented, what is partially implemented, what is missing, and what we should complete next phase by phase.

The goal is simple: before adding more code, we should know exactly where the project stands.

---

## 1. Current Project Summary

The project is now a Laravel-based Hospital Management System with:

- Web-based Blade frontend.
- API routes for backend modules.
- Laravel Sanctum API authentication.
- Spatie roles and permissions.
- Role-based module access.
- Department-aware dashboard and reports.
- Dynamic dashboard data.
- Real-time notification foundation using Laravel notifications and Pusher/Echo.
- Admin workflow for appointment status approval.
- System backups and audit/login logs.

The project is no longer only an API backend. It now has a working frontend structure for hospital staff roles.

---

## 2. Existing Documentation

Current docs already present:

- `docs/FRONTEND_PHASES.md`
- `docs/TEST_MATRIX.md`
- `docs/ROLE_ACCESS_MATRIX.md`

This new document should be used as the updated master audit and planning document.

Recommended future docs:

- `docs/ROLE_ACCESS_MATRIX.md`
- `docs/DATABASE_RELATIONSHIPS.md`
- `docs/NOTIFICATION_FLOW.md`
- `docs/DEPLOYMENT_GUIDE.md`
- `docs/API_REFERENCE.md`

---

## 3. Current Technology Stack

Implemented stack:

- Laravel 12 style application.
- Blade frontend.
- Laravel Sanctum.
- Spatie Laravel Permission.
- Session-based web authentication.
- API authentication through Sanctum.
- Pusher/Echo notification setup.
- MySQL-style relational database structure.
- Dashboard charts rendered with custom SVG/JavaScript.

No new dependency is required for the next planning phase unless we decide to add:

- PDF export.
- Excel export.
- Full calendar UI.
- Swagger/OpenAPI documentation.
- Advanced chart library.

---

## 4. Current Roles

Existing roles:

- `super_admin`
- `admin`
- `doctor`
- `nurse`
- `receptionist`
- `lab_technician`
- `pharmacist`
- `accountant`
- `hr_manager`
- `user`

Cleaned in Phase A:

- `view logs`
- `manage backups`
- `manage security`
- `view backups`

These names are now treated as permissions only, not roles.

Final admin hierarchy:

- `super_admin` is the global owner and can manage all departments, elevated roles, backups, logs, and security-level workflows.
- `admin` is a department admin, not a second super admin.
- Department admins can manage only same-department operational users and department workflow.
- Department admins cannot assign or remove `super_admin` or `admin` roles.
- System backups and audit logs are reserved for `super_admin`.

---

## 5. Current Departments

Seeded departments:

- Administration
- OPD
- Reception
- Laboratory
- Pharmacy
- Finance
- HR
- IT
- Wards

These are good for the current project.

Possible future departments:

- Emergency
- Radiology
- ICU
- Surgery
- Pediatrics
- Cardiology
- Orthopedics

Only add these if the system needs those workflows.

---

## 6. Current Permission System

Current permissions include:

- `manage users`
- `manage roles`
- `manage departments`
- `view patients`
- `edit patients`
- `create appointments`
- `manage prescriptions`
- `manage lab tests`
- `manage billing`
- `manage medicines`
- `dispense prescriptions`
- `manage staff`
- `view staff`
- `assign shifts`
- `view shifts`
- `manage backups`
- `view backups`
- `view logs`
- `manage security`

Status:

- Basic RBAC is implemented.
- Routes mainly use role middleware.
- Some API routes use permission middleware.
- Dashboard and Reports now use role/department-based visibility.
- Public registration is disabled; user creation is handled from Admin Users.

Recommended improvement:

- Move toward permission-based checks for actions, not only roles.
- Keep roles as job titles.
- Keep permissions as abilities.

Example:

- Role: `receptionist`
- Permissions: `view patients`, `edit patients`, `create appointments`

---

## 7. Existing Web Modules

### 7.1 Authentication

Implemented:

- Login page.
- Register page.
- Forgot password page.
- Reset password page.
- Logout.
- Modern auth layout.

Status: Working.

Possible improvements:

- Disable public registration in production.
- Let only admin create staff accounts.
- Add email verification.
- Add password strength rules.

---

### 7.2 Dashboard

Implemented:

- Single main dashboard for all roles.
- Admin-specific header card inside normal dashboard.
- Role/department-based widgets.
- Live `/dashboard/data` endpoint.
- Dynamic charts.
- Recent appointments.
- Notifications section.
- Bed status.
- Pharmacy sales cards.
- Staff cards.
- Billing cards.

Important completed correction:

- Separate `/admin/dashboard` was removed.
- Normal `/dashboard` is now the main dashboard for everyone.
- For `super_admin` and `admin`, normal dashboard acts like admin dashboard.

Current role behavior:

- Admin/Super Admin: broad hospital activity.
- Receptionist: patients and appointment flow.
- Doctor: patients, appointments, lab tests, beds.
- Nurse: patients, appointments, wards/beds.
- Lab Technician: lab test data.
- Pharmacist: low stock, medicine sales quantity, medicine sales amount.
- Accountant: revenue and pending invoices.
- HR Manager: active staff.

Status: Strong foundation.

Possible improvements:

- Add role-specific quick action buttons.
- Add date filters on dashboard.
- Add departmental dashboard filter for super admin.
- Add better empty-state messages per role.

---

### 7.3 Patients

Implemented:

- List patients.
- Create patient.
- Show patient.
- Edit patient.
- Delete patient.
- Search/filter.
- Department relationship.
- Notifications on patient creation/update.

Roles:

- `super_admin`
- `admin`
- `doctor`
- `nurse`
- `receptionist`

Status: Functional.

Missing or recommended:

- Patient unique MRN/registration number.
- Date of birth or age.
- Blood group.
- Emergency contact.
- CNIC/National ID.
- Patient allergies.
- Patient insurance info.
- Patient documents/uploads.
- Patient visit history.
- Patient duplicate detection.
- Soft deletes instead of permanent delete.

Priority: High.

---

### 7.4 Appointments

Implemented:

- List appointments.
- Create appointment.
- Show appointment.
- Edit appointment.
- Delete appointment.
- Statuses: `pending`, `approved`, `completed`, `cancelled`.
- Admin appointment approval panel.
- Dashboard appointment metrics.
- Appointment reports.
- Notifications.

Roles:

- `super_admin`
- `admin`
- `doctor`
- `nurse`
- `receptionist`

Status: Good.

Missing or recommended:

- Appointment reason/complaint field.
- Appointment notes.
- Check-in/check-out status.
- Doctor availability schedule.
- Prevent double booking.
- Calendar view.
- Appointment reschedule workflow.
- Patient appointment history.
- SMS/email reminders.

Priority: High.

---

### 7.5 Lab Tests

Implemented:

- List lab tests.
- Create lab test.
- Show lab test.
- Edit lab test.
- Delete lab test.
- Statuses: `pending`, `in_process`, `completed`.
- Lab test reports.
- Dashboard lab pending count.

Roles:

- `super_admin`
- `admin`
- `doctor`
- `lab_technician`

Status: Functional.

Missing or recommended:

- Lab result file upload.
- Result approval/verification.
- Normal range fields.
- Test category/catalog.
- Lab sample collection status.
- Doctor comments on lab result.
- Patient-facing printable report.
- Audit trail for result changes.

Priority: High.

---

### 7.6 Prescriptions

Current status:

- Model and migration exist.
- API/controller support appears partial.
- Full Blade UI does not appear implemented as a main module.
- Pharmacy API has pending prescription and dispense methods.

Missing:

- Web UI for doctor prescription creation.
- Prescription details page.
- Medicine selection from inventory.
- Dosage/frequency/duration fields.
- Prescription print/download.
- Dispense workflow tied to medicine stock.
- Prescription status in database migration may need review because controller expects `status`.

Priority: Very High.

Reason:

Doctor workflow is incomplete without prescriptions.

---

### 7.7 Pharmacy / Medicines

Implemented:

- List medicines.
- Create medicine.
- Show medicine.
- Edit medicine.
- Delete medicine.
- Stock.
- Price.
- Expiry date.
- Status: `available`, `unavailable`.
- Pharmacy report.
- Low stock dashboard card.
- Medicine quantity sold dashboard card.
- Medicine sales amount dashboard card.
- Top-selling medicines in report.

Roles:

- `super_admin`
- `admin`
- `pharmacist`

Status: Good for inventory.

Missing or recommended:

- Medicine sales should link to `medicine_id`, not only `billing_items.service_name`.
- Stock should reduce automatically when medicine is sold/dispensed.
- Batch number.
- Supplier.
- Purchase price.
- Sale price history.
- Expiry alerts.
- Stock adjustment logs.
- Purchase order module.
- Pharmacy dispense workflow connected to prescriptions.

Priority: High.

---

### 7.8 Billing

Implemented:

- Create invoice.
- Add invoice items.
- Item types: `lab`, `medicine`, `appointment`, `other`.
- Mark invoice paid.
- Cancel invoice.
- Billing reports.
- Pending invoices dashboard card.
- Revenue dashboard card.

Roles:

- `super_admin`
- `admin`
- `accountant`

Status: Functional.

Missing or recommended:

- Invoice number format.
- Payment method.
- Partial payments.
- Discounts.
- Tax.
- Refunds.
- Printable invoice.
- Link billing items directly to module records.
- Insurance claim support.
- Receipt generation.

Priority: Medium to High.

---

### 7.9 Staff

Implemented:

- Staff list.
- Create staff profile.
- Show staff profile.
- Edit staff profile.
- Delete staff profile.
- Department and user relationship.
- Employment statuses: `active`, `terminated`, `resigned`.
- Staff reports.

Roles:

- `super_admin`
- `admin`
- `hr_manager`

Status: Functional.

Missing or recommended:

- Staff attendance.
- Leave management.
- Payroll.
- Contract/document upload.
- Performance notes.
- Staff emergency contact.
- Shift calendar.

Priority: Medium.

---

### 7.10 Shifts

Implemented:

- Shift list.
- Assign shift.
- Shift names: Morning, Evening, Night.

Roles:

- `super_admin`
- `admin`
- `hr_manager`
- `doctor` can view.

Status: Basic.

Missing or recommended:

- Date-range shifts.
- Recurring shifts.
- Shift conflict detection.
- Department filter.
- Staff availability.
- Calendar view.

Priority: Medium.

---

### 7.11 Wards & Beds

Implemented:

- Wards CRUD.
- Beds CRUD.
- Bed allocations.
- Assign bed.
- Release bed.
- Transfer bed.
- Bed statuses: `available`, `occupied`, `maintenance`.
- Ward/bed reports.
- Bed dashboard cards.

Roles:

- `super_admin`
- `admin`
- `nurse`
- `doctor`

Status: Good foundation.

Missing or recommended:

- Admission/discharge module.
- Bed allocation history page per patient.
- Ward capacity warnings.
- Bed maintenance workflow.
- ICU/general/private ward categories.
- Nurse station view.

Priority: Medium to High.

---

### 7.12 Reports

Implemented:

- Reports landing page.
- Role-based report visibility.
- Patient report.
- Appointment report.
- Billing report.
- Lab test report.
- Pharmacy report.
- Ward/bed report.
- Staff report.

Status: Good.

Missing or recommended:

- Export PDF.
- Export Excel/CSV.
- Date filters on every report.
- Department filter for super admin/admin.
- Printable layouts.
- Charts inside reports.
- Saved report presets.

Priority: Medium.

---

### 7.13 Notifications

Implemented:

- Database notifications.
- Pusher/Echo setup.
- Notification dropdown.
- Mark all read.
- Individual notification mark-read on click.
- Notification service catches broadcast failures.
- Notifications are triggered from several modules.

Status: Good foundation.

Missing or recommended:

- Notification preferences per role/user.
- Dedicated notifications page.
- Better notification categories.
- Retry/queue broadcast notifications.
- Notification history filters.
- Role-specific notification rules document.

Priority: Medium.

---

### 7.14 Search

Implemented:

- Navbar live search.
- Search results across modules.
- Search dropdown with `Searching...` state.

Status: Good.

Missing or recommended:

- Permission-aware search result filtering.
- Keyboard navigation.
- Highlight matched text.
- Search result grouping by module.

Priority: Medium.

---

### 7.15 Admin Users

Implemented:

- Manage user roles.
- Manage user departments.
- Create users from admin panel.
- Create doctor/staff accounts before assigning availability.
- Optional staff profile creation during user creation.

Roles:

- `super_admin`
- `admin`

Status: Basic but useful.

Missing or recommended:

- Admin creates users/staff instead of public register. ✅
- User activate/deactivate.
- Reset user password.
- User profile page.
- Role assignment validation by department. ✅
- Department admin restrictions in web routes. ✅

Current user creation rules:

- `super_admin` can create users for any department, including department admins.
- `admin` can create only same-department non-admin users.
- Doctor users should be created here before adding doctor availability slots.
- Staff profile can be created together with the user when needed.

Priority: High.

---

### 7.16 Backups and Audit Logs

Implemented:

- Backup index/create/download.
- Activity logs view.
- Login logs view.

Roles:

- `super_admin`
- `admin`

Status: Basic.

Missing or recommended:

- Automated scheduled backups.
- Backup retention policy.
- Download authorization hardening.
- More complete activity logging on create/update/delete.
- Audit trail per record.

Priority: Medium.

---

## 8. Current API Modules

API routes exist for:

- Admin user management.
- Patients.
- Appointments.
- Lab tests.
- Pharmacy and prescriptions.
- Billing.
- Staff.
- Shifts.
- Wards.
- Beds.
- Bed allocations.
- Reports.
- Backups.
- Logs.

Status:

- API exists and has role protection.
- Some API modules may be older than the current Blade/web modules.
- Web and API logic are not fully unified.

Recommended:

- Decide whether APIs are production-supported or internal only.
- Align API controllers with current web behavior.
- Add API resources consistently.
- Add API documentation.
- Add API tests.

Priority: Medium.

---

## 9. Current UI Status

Implemented UI areas:

- Auth pages.
- Main layout/sidebar/navbar.
- Dashboard.
- Patients.
- Appointments.
- Lab tests.
- Medicines.
- Billing.
- Staff.
- Shifts.
- Wards/beds.
- Reports.
- Admin users.
- Admin appointment approval.
- Backups/logs.

Status: Strong frontend coverage.

Missing UI areas:

- Full prescriptions UI.
- Dedicated notifications page.
- Profile/settings page.
- Department management UI.
- Role/permission management UI.
- Medical records UI.
- Patient admission/discharge UI.

---

## 10. Major Missing Hospital Management Features

These are important if the system should become a more complete HMS.

### 10.1 Medical Records

Missing:

- Diagnosis.
- Visit notes.
- Vitals.
- Medical history.
- Allergies.
- Attachments.
- Doctor notes.

Priority: Very High.

Recommended module:

- `MedicalRecord`
- Related to patient, doctor, appointment.

---

### 10.2 Prescriptions Complete Workflow

Missing:

- Doctor creates prescription.
- Pharmacist dispenses prescription.
- Stock reduces after dispense.
- Prescription print.

Priority: Very High.

---

### 10.3 Admission / Discharge

Missing:

- Admit patient.
- Assign ward/bed.
- Transfer.
- Discharge summary.
- Final billing.

Priority: High.

---

### 10.4 Doctor Schedule

Missing:

- Doctor availability.
- Appointment slot system.
- Prevent double booking.
- Leave/unavailable dates.

Priority: High.

---

### 10.5 Pharmacy Purchase and Stock Ledger

Missing:

- Stock purchase.
- Supplier.
- Batch.
- Expiry.
- Stock adjustment.
- Stock ledger.

Priority: High.

---

### 10.6 Payment System

Missing:

- Partial payments.
- Payment method.
- Receipt.
- Refund.
- Discount.

Priority: Medium.

---

### 10.7 Documents and Uploads

Missing:

- Lab report file uploads.
- Patient documents.
- Staff documents.
- Prescription attachments.

Priority: Medium.

---

### 10.8 Patient Portal

Missing:

- Patient login.
- Appointment request.
- View prescriptions.
- View lab reports.
- View invoices.

Priority: Optional / Future.

---

## 11. Data Model Gaps

Current models are good for the implemented modules, but the following improvements are recommended:

### Patient

Add:

- `mrn`
- `date_of_birth`
- `blood_group`
- `emergency_contact_name`
- `emergency_contact_phone`
- `cnic`
- `allergies`

### Appointment

Add:

- `reason`
- `notes`
- `checked_in_at`
- `completed_at`
- `cancelled_reason`

### Prescription

Improve:

- Add `status`
- Add structured prescription items table.
- Avoid storing all medicines as a plain string.

Recommended:

- `prescription_items`
- Columns: `prescription_id`, `medicine_id`, `dosage`, `frequency`, `duration`, `quantity`, `instructions`

### Billing Items

Improve:

- Add nullable relation fields:
  - `medicine_id`
  - `lab_test_id`
  - `appointment_id`

Reason:

- Current pharmacy sales report depends on `service_name`.
- Better reporting needs direct relation with medicines.

### Medicines

Add:

- `supplier_id`
- `batch_number`
- `purchase_price`
- `reorder_level`

---

## 12. Security and Compliance Gaps

Implemented:

- Auth middleware.
- Role middleware.
- Sanctum.
- Password reset.
- Login/logout.
- Rate limit on login.

Recommended:

- Disable public registration for production.
- Encrypt sensitive patient fields if needed.
- Add full activity audit on all important writes.
- Add soft deletes for patient/medical/billing records.
- Add policies for department ownership.
- Add two-factor authentication for admins.
- Add stronger password rules.
- Add session timeout policy.

Priority: High before production.

---

## 13. Testing Status

Existing:

- `docs/TEST_MATRIX.md`

Recommended tests:

- Auth tests.
- Role access tests.
- Dashboard visibility tests.
- Report visibility tests.
- Patient CRUD tests.
- Appointment status workflow tests.
- Billing payment/cancel tests.
- Pharmacy sales report tests.
- Notification read/unread tests.

Priority: High.

---

## 14. Recommended Next Phases

### Phase A — Cleanup and RBAC Stabilization

Tasks:

- Remove permission-like roles if not needed. ✅
- Create final role access matrix doc. ✅
- Add route access tests. ✅
- Disable public registration or make it admin-only. ✅

Recommended first because it prevents future security confusion.

Status: completed on 2026-06-11.

---

### Phase B — Patient and Appointment Hardening

Tasks:

- Add MRN to patients. ✅
- Add appointment reason/notes. ✅
- Add check-in workflow. ✅
- Add doctor availability. ✅
- Prevent double booking. ✅

Phase B step 1 status:

- Patient MRN database column added.
- MRN auto-generates when not entered.
- MRN is unique and searchable.
- Patient list/show/form pages display MRN.
- API and web validation now support MRN.

Phase B step 2 status:

- Appointment reason/complaint field added.
- Appointment notes field added.
- Appointment create/edit/show/list pages support reason and notes.
- Admin appointment approval panel shows/searches reason.
- Appointment reports and global search include reason context.

Phase B step 3 status:

- Appointment check-in timestamp added.
- Appointment check-out timestamp added.
- Only approved appointments can be checked in.
- Checked-out approved appointments are automatically marked completed.
- Appointment list/show/admin/report pages display visit flow status.
- Web and API endpoints exist for check-in/check-out.

Phase B step 4 status:

- Doctor availability table added.
- Weekly doctor availability slots can be managed from the web UI.
- Doctors can manage/view their own availability.
- Department admins can manage same-department doctor availability.
- Super admin can manage all doctor availability.
- Appointment booking now validates selected doctor availability.
- API appointment validation also checks doctor availability.

Phase B step 5 status:

- Doctor double booking prevention added.
- Active appointments block the same doctor/date/time slot.
- Cancelled appointments do not block replacement bookings.
- Updating an appointment ignores its own current slot.
- Web and API appointment validation both enforce the conflict rule.

Phase B status: completed.

---

### Phase C — Prescriptions and Medical Records

Tasks:

- Build full prescriptions UI. ✅
- Add prescription items. ✅
- Add medical records. ✅
- Connect doctor workflow:
  - appointment
  - medical record
  - prescription
  - lab request ✅

This is the most important missing clinical workflow.

Phase C step 1 status:

- Prescription Blade module added.
- Prescription list/create/show/edit/delete pages added.
- Prescription status column added for pending/dispensed/cancelled workflow.
- Doctor users see only their own prescriptions.
- Department admins see department patient prescriptions.
- Super admin can manage all prescriptions.
- Existing simple medicines text field remains until prescription items are added in the next step.

Phase C step 2 status:

- Structured `prescription_items` table added.
- Prescription create/edit forms now support medicine item rows.
- Items can link to inventory medicines or use a custom medicine name.
- Dosage, frequency, duration, quantity, and instructions are stored per item.
- Prescription list/show pages display structured medicine items.
- Legacy free-text medicines note remains for old records and compatibility.

Phase C step 3 status:

- Medical records table added.
- Medical Records Blade module added.
- Doctors, department admins, and super admin can create/view/update/delete medical records.
- Medical records can link patient, doctor, and appointment.
- Diagnosis, vitals, history, allergies, notes, visit type, and follow-up date are stored.
- Doctors see only their own medical records.
- Department admins see same-department patient records.

Phase C step 4 status:

- Appointment details now provide direct workflow actions.
- Doctors/admins can start a medical record from an appointment.
- Doctors/admins can start a prescription from an appointment.
- Doctors/admins/lab users can start a lab request from an appointment.
- Appointment details now include a patient history shortcut plus a compact care workflow panel.
- Appointment details now show a check-in → record → prescription → lab → billing timeline.
- Patient history now shows the same care workflow timeline per appointment row.
- Medical record, prescription, and lab test create forms prefill patient/doctor/appointment context.
- Medical record forms now show linked appointment, patient, doctor, reason, and notes context more clearly.
- Prescription forms now show linked appointment, patient, doctor, reason, and notes context more clearly.
- Lab test forms now show linked appointment, patient, doctor, reason, and notes context more clearly.
- Billing forms now show linked appointment context and prefill the first invoice line when available.
- Billing detail and receipt pages now show a source chain for invoice items.
- Billing detail and receipt pages now show payment progress and latest payment snapshot.
- Medical record details now provide next-step actions for prescription and lab request.
- Patient medical history page now shows appointments, medical records, and prescriptions in one view.

---

### Phase D — Pharmacy Dispense and Stock Ledger

Tasks:

- Link prescription to medicine inventory. ✅
- Reduce stock on dispense. ✅
- Add stock ledger. ✅
- Add expiry and reorder alerts. ✅

Phase D status:

- Prescription items can now link to inventory medicines.
- Pharmacy dispense queue shows pending prescriptions.
- Dispensing a prescription reduces medicine stock inside a database transaction.
- Every dispense creates a stock movement ledger entry.
- Opening stock is logged when a medicine is created.
- Manual stock edits now create adjustment ledger entries.
- Medicine detail pages show recent stock movements.
- Stock ledger page shows opening, adjustment, and dispense history.
- Expiry and reorder alerts now surface on the medicine list page.
- Reorder thresholds are configurable per medicine.
- A pharmacy alert command exists for sending expiry/reorder notifications.

---

### Phase E - Billing and Payments Upgrade

Tasks:

- Add invoice number. ✅
- Add payment method. ✅
- Add partial payments. ✅
- Add printable receipts. ✅
- Link billing items to source records. ✅

Phase E status:

- Billing records now generate unique invoice numbers.
- Payments can be stored with method, amount, reference, and notes.
- Partial payments update paid amount, balance due, and invoice status.
- Printable receipt view is available from the billing detail page.
- Billing items can link to source records such as appointments, lab tests, medicines, and medical records.
- API billing endpoints now follow the same invoice/payment structure.

### Phase F - Reports and Exports

Tasks:

- PDF export. ?
- Excel/CSV export. ?
- Department filters. ?
- Charts. ?
- Print-friendly report pages. ?

Phase F status:

- Report pages now expose CSV and PDF export actions.
- Department filters are available on role-aware report pages.
- Simple chart sections are shown in the report views.
- Print-friendly controls are added to the reports pages.
- The reports controller now supports export responses for web reports.

### Phase G - Production Readiness

Tasks:

- Full test coverage. [x]
- API documentation. [x]
- Deployment guide. [x]
- Backup automation. [x]
- Queue notifications. [x]
- Security hardening. [x]

Phase G status:

- Core billing, reports, backups, and notifications now have focused tests.
- API reference and deployment guide documents are added.
- Daily backup and pharmacy alert scheduling is registered.
- Hospital notifications now implement queueable delivery.
- Backup downloads are path-validated and backup retention is automated.
- Production test coverage was expanded for reports and system tools.

---

## 15. Recommended Immediate Next Task

The best next task is:

## Build Full Prescriptions + Medical Records Workflow

Reason:

- The system already has patients, appointments, lab tests, pharmacy, and billing.
- The biggest hospital workflow gap is the doctor clinical flow.
- A doctor needs to:
  - open appointment
  - write diagnosis/medical notes
  - request lab tests
  - create prescription
  - send medicine to pharmacy

Recommended implementation order:

1. Add `medical_records` table.
2. Add `prescription_items` table.
3. Build doctor prescription UI.
4. Build patient medical history page.
5. Connect pharmacy dispense to prescription items.
6. Reduce medicine stock on dispense.

---

## 16. Final Audit Result

The project is in a good state as a functional HMS prototype with strong frontend coverage.

It already includes:

- Auth
- Role access
- Dashboard
- Patients
- Appointments
- Lab tests
- Pharmacy inventory
- Billing
- Staff
- Shifts
- Wards/beds
- Reports
- Notifications
- Search
- Admin user management
- Backups/logs

The most important missing areas are:

1. Medical records.
2. Full prescription workflow.
3. Pharmacy dispense with stock deduction.
4. Doctor availability and appointment conflict prevention.
5. Patient MRN and richer patient profile.
6. Billing/payment upgrades.
7. Production security and tests.

This document should be reviewed first, then we can implement the next phase step by step.
