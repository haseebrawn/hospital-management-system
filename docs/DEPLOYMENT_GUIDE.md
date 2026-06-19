# HMS Deployment Guide

This guide covers the current production-ready setup for the HMS project.

## Required Environment

- PHP 8.2+
- Composer
- Node.js and npm
- MySQL or compatible database
- Queue worker
- Cron/scheduler access

## Deployment Steps

1. Pull the latest code.
2. Install dependencies:
   - `composer install --no-dev --optimize-autoloader`
   - `npm install`
   - `npm run build`
3. Configure `.env` values:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `DB_CONNECTION=mysql`
   - queue and broadcast settings
4. Run migrations:
   - `php artisan migrate --force`
5. Seed only if required:
   - `php artisan db:seed --force`
6. Clear and cache config:
   - `php artisan config:cache`
   - `php artisan route:cache`
   - `php artisan view:cache`
7. Start queue workers:
   - `php artisan queue:work`
8. Enable the scheduler cron:
   - `php artisan schedule:run`
   - or a system cron entry that runs every minute

## Automated Jobs

- Daily database backups run through `db:backup`.
- Daily pharmacy alerts run through `pharmacy:send-alerts`.

## Post-Deploy Checks

- Login works.
- Dashboard loads.
- Reports pages render.
- Backups can be created and downloaded.
- Notifications appear for role-based events.
- Queue workers process jobs without errors.
