# HMS API Reference

This document is a lightweight reference for the current Laravel HMS API.

## Authentication

- `POST /login`
- `POST /logout`
- `POST /forgot-password`
- `POST /reset-password`

## Core Modules

- Patients: `GET /api/patients`, `POST /api/patients`
- Appointments: `GET /api/appointments`, `POST /api/appointments`
- Lab tests: `GET /api/lab-tests`, `POST /api/lab-tests`
- Prescriptions: `GET /api/prescriptions`, `POST /api/prescriptions`
- Medicines: `GET /api/medicines`, `POST /api/medicines`
- Billing: `GET /api/billing`, `POST /api/billing`
- Staff: `GET /api/staff`, `POST /api/staff`
- Shifts: `GET /api/shifts`, `POST /api/shifts`
- Wards and beds: `GET /api/wards`, `GET /api/beds`

## Billing Notes

- Invoice numbers are generated automatically.
- Partial payments are stored through the billing payments endpoint.
- Billing items can link back to source records.

## Reports

- `GET /reports/patients`
- `GET /reports/appointments`
- `GET /reports/billing`
- `GET /reports/lab-tests`
- `GET /reports/pharmacy`
- `GET /reports/ward-bed`
- `GET /reports/staff`

Supported query options:

- `from`
- `to`
- `department_id`
- `export=csv`
- `export=pdf`

## Permissions

API routes use role and permission middleware. Common roles:

- `super_admin`
- `admin`
- `doctor`
- `receptionist`
- `lab_technician`
- `pharmacist`
- `accountant`

