# Hospital Management System — Role Access Matrix

Date: 2026-06-11

This document is the final RBAC guide for the current project flow. Roles are job titles. Permissions are abilities. Permission-like names such as `view logs`, `manage backups`, `manage security`, and `view backups` must stay as permissions only, not roles.

## Core Roles

| Role | Department Scope | Main Purpose |
| --- | --- | --- |
| `super_admin` | All departments | Full platform ownership and unrestricted hospital administration. |
| `admin` | Own department only | Department-level management, approvals, same-department user control, and operational visibility. |
| `doctor` | Assigned/own department clinical data | Patient review, appointments, prescriptions, lab requests, and clinical workflow. |
| `nurse` | Ward/patient flow | Patient support, appointment coordination, ward and bed workflow. |
| `receptionist` | Reception/patient flow | Patient registration, appointment booking, and front-desk reports. |
| `lab_technician` | Laboratory | Lab test processing, result updates, and lab reports. |
| `pharmacist` | Pharmacy | Medicine inventory, prescription dispensing, and pharmacy reports. |
| `accountant` | Finance | Billing, payments, invoices, and revenue reports. |
| `hr_manager` | HR/staff | Staff records, shifts, and staff reports. |
| `user` | Minimal | Default low-access role; should not manage hospital modules. |

## Module Access

| Module | Super Admin | Admin | Doctor | Nurse | Receptionist | Lab Tech | Pharmacist | Accountant | HR Manager |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Dashboard | Full | Department/admin view | Clinical view | Ward/patient view | Front-desk view | Lab view | Pharmacy view | Finance view | HR view |
| Patients | Full CRUD | Department CRUD | View/update | View/update | Create/update | No | No | No | No |
| Appointments | Full CRUD/status | Department CRUD/status | View/update | Create/update | Create/update | No | No | No | No |
| Lab Tests | Full CRUD | Department CRUD | Request/view | No | No | Process/update | No | No | No |
| Pharmacy | Full CRUD | Department CRUD | View as needed | No | No | No | Medicine CRUD/dispense | No | No |
| Billing | Full CRUD | Department CRUD | No | No | No | No | No | Billing/payments | No |
| Staff | Full CRUD | Department CRUD | View staff/shifts | No | No | No | No | No | Staff CRUD |
| Shifts | Full | Department | View | No | No | No | No | No | Assign/manage |
| Wards & Beds | Full | Department | View/assign workflow | Ward workflow | No | No | No | No | No |
| Reports | Full | Department/admin reports | Clinical reports | Patient/ward reports | Patient/appointment reports | Lab reports | Pharmacy reports | Billing reports | Staff reports |
| Backups | Full | No | No | No | No | No | No | No | No |
| Audit Logs | Full | No | No | No | No | No | No | No | No |

## Permissions By Role

| Role | Permissions |
| --- | --- |
| `super_admin` | All permissions. |
| `admin` | `manage users`, `view patients`, `edit patients`, `create appointments`, `manage prescriptions`, `manage lab tests`, `manage billing`, `manage medicines`, `dispense prescriptions`, `manage staff`, `view staff`, `assign shifts`, `view shifts` |
| `doctor` | `view patients`, `edit patients`, `manage prescriptions`, `manage lab tests`, `view staff`, `view shifts` |
| `nurse` | `view patients`, `edit patients`, `create appointments`, `view shifts` |
| `receptionist` | `view patients`, `edit patients`, `create appointments` |
| `lab_technician` | `manage lab tests` |
| `pharmacist` | `manage medicines`, `dispense prescriptions` |
| `accountant` | `manage billing` |
| `hr_manager` | `manage staff`, `view staff`, `assign shifts`, `view shifts` |
| `user` | No operational permissions by default. |

## Route Guarding Rule

- Use role middleware for high-level module entry.
- Use FormRequest authorization and policies/services for action-level decisions.
- Use permission middleware for abilities such as backups, logs, billing, and medicine management.
- Keep admin-only user creation inside the authenticated admin panel; public registration is disabled.
- `super_admin` can manage every department and can assign elevated roles.
- `admin` can manage only users in their own department and cannot assign or remove `super_admin` or `admin` roles.
- `super_admin` can create department admins and doctors for any department.
- `admin` can create doctors/staff only for their own department.
- System backups, audit logs, and global security controls are `super_admin` only.

## Next RBAC Improvements

- Move more write actions from role checks to permission checks.
- Add policy classes for ownership-sensitive resources.
- Keep report visibility centralized through `DashboardScopeService`.
- Add department-aware tests for admin versus super admin behavior.

## Current Hardening Notes

- Patient records now use a unique MRN/registration number.
- If staff leave MRN empty, the system auto-generates it.
- MRN search is available in patient listing and global search.
- Appointment records now include reason/complaint and notes for reception/admin/doctor context.
- Appointment visit flow now tracks check-in and check-out; only approved appointments can be checked in.
- Doctor availability slots define when appointments can be booked for a selected doctor.
- Appointment booking prevents double booking the same doctor at the same date and time.
- Doctor user accounts are created from Admin Users before adding availability slots.
- Prescription UI exists for doctor/admin/super admin workflow; structured prescription items are the next Phase C step.
