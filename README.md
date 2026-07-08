# Hospital Management System

Laravel 12 hospital management platform with role-based dashboards, clinical workflows, billing, lab, pharmacy, reports, notifications, and real-time UI updates.

## Stack
- `laravel/framework` for the backend web app
- `laravel/sanctum` for API authentication
- `spatie/laravel-permission` for roles and permissions
- `pusher/pusher-php-server` for real-time notifications
- `barryvdh/laravel-dompdf` for PDF receipts and print views
- `maatwebsite/excel` for CSV / Excel exports

## Main Modules
- Dashboard and role-based shell
- Patients and appointments
- Medical records and prescriptions
- Lab tests
- Pharmacy dispense and stock ledger
- Billing, receipts, and payments
- Reports and exports
- Notifications and audit-related utilities

## Short Project Summary
- Super admin and admin manage overall access and workflow.
- Department roles handle their own module data through scoped dashboards.
- Clinical flow connects appointment → medical record → prescription → lab → billing.
- Shared UI spacing and workflow timelines keep pages visually consistent.

## Run Locally
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

## Useful Commands
```bash
php artisan test
npm run dev
npm run build
```

## Notes
- This project uses Laravel Blade for the frontend shell and module pages.
- The current documentation workflow keeps detailed `.md` files local unless explicitly allowed for Git.
