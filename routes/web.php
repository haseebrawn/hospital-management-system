<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Web\PatientsController;
use App\Http\Controllers\Web\AppointmentsController;
use App\Http\Controllers\Web\DoctorAvailabilitiesController;
use App\Http\Controllers\Web\LabTestsController;
use App\Http\Controllers\Web\PrescriptionsController;
use App\Http\Controllers\Web\PharmacyDispensesController;
use App\Http\Controllers\Web\PharmacyLedgerController;
use App\Http\Controllers\Web\MedicalRecordsController;
use App\Http\Controllers\Web\MedicinesController;
use App\Http\Controllers\Web\BillingController as WebBillingController;
use App\Http\Controllers\Web\StaffController;
use App\Http\Controllers\Web\ShiftsController;
use App\Http\Controllers\Web\WardsBedsController;
use App\Http\Controllers\Web\ReportsController as WebReportsController;
use App\Http\Controllers\Web\Admin\UsersController as AdminUsersController;
use App\Http\Controllers\Web\Admin\AppointmentsController as AdminAppointmentsController;
use App\Http\Controllers\Web\System\BackupsController as SystemBackupsController;
use App\Http\Controllers\Web\System\LogsController as SystemLogsController;
use App\Http\Controllers\Web\DashboardDataController;
use App\Http\Controllers\Web\NotificationsController;
use App\Http\Controllers\Web\SearchController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1')
        ->name('login.post');

    Route::get('/register', [AuthController::class, 'registrationDisabled'])->name('register');
    Route::post('/register', [AuthController::class, 'registrationDisabled'])->name('register.post');

    // Forgot / Reset Password (web)
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendPasswordResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'updatePassword'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', DashboardDataController::class)->name('dashboard.data');
    Route::get('/search', SearchController::class)->name('search');
    Route::get('/notifications', [NotificationsController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationsController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationsController::class, 'markRead'])->name('notifications.read');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    /*
    |--------------------------------------------------------------------------
    | Web UI Modules (Phase-wise)
    |--------------------------------------------------------------------------
    */

    Route::prefix('patients')
        ->middleware('role:super_admin|admin|doctor|nurse|receptionist')
        ->group(function () {
            Route::get('/', [PatientsController::class, 'index'])->name('patients.index');
            Route::get('/create', [PatientsController::class, 'create'])->name('patients.create');
            Route::post('/', [PatientsController::class, 'store'])->name('patients.store');
            Route::get('/{patient}', [PatientsController::class, 'show'])->name('patients.show');
            Route::get('/{patient}/edit', [PatientsController::class, 'edit'])->name('patients.edit');
            Route::put('/{patient}', [PatientsController::class, 'update'])->name('patients.update');
            Route::delete('/{patient}', [PatientsController::class, 'destroy'])->name('patients.destroy');
        });

    Route::prefix('appointments')
        ->middleware('role:super_admin|admin|doctor|nurse|receptionist')
        ->group(function () {
            Route::get('/', [AppointmentsController::class, 'index'])->name('appointments.index');
            Route::get('/create', [AppointmentsController::class, 'create'])->name('appointments.create');
            Route::post('/', [AppointmentsController::class, 'store'])->name('appointments.store');
            Route::get('/doctor-availability', [DoctorAvailabilitiesController::class, 'index'])->name('doctor-availabilities.index');
            Route::get('/doctor-availability/create', [DoctorAvailabilitiesController::class, 'create'])->name('doctor-availabilities.create');
            Route::post('/doctor-availability', [DoctorAvailabilitiesController::class, 'store'])->name('doctor-availabilities.store');
            Route::get('/doctor-availability/{doctorAvailability}/edit', [DoctorAvailabilitiesController::class, 'edit'])->name('doctor-availabilities.edit');
            Route::put('/doctor-availability/{doctorAvailability}', [DoctorAvailabilitiesController::class, 'update'])->name('doctor-availabilities.update');
            Route::delete('/doctor-availability/{doctorAvailability}', [DoctorAvailabilitiesController::class, 'destroy'])->name('doctor-availabilities.destroy');
            Route::get('/{appointment}', [AppointmentsController::class, 'show'])->name('appointments.show');
            Route::get('/{appointment}/edit', [AppointmentsController::class, 'edit'])->name('appointments.edit');
            Route::put('/{appointment}', [AppointmentsController::class, 'update'])->name('appointments.update');
            Route::put('/{appointment}/check-in', [AppointmentsController::class, 'checkIn'])->name('appointments.check-in');
            Route::put('/{appointment}/check-out', [AppointmentsController::class, 'checkOut'])->name('appointments.check-out');
            Route::delete('/{appointment}', [AppointmentsController::class, 'destroy'])->name('appointments.destroy');
        });

    Route::prefix('lab-tests')
        ->middleware('role:super_admin|admin|lab_technician|doctor')
        ->group(function () {
            Route::get('/', [LabTestsController::class, 'index'])->name('lab-tests.index');
            Route::get('/create', [LabTestsController::class, 'create'])->name('lab-tests.create');
            Route::post('/', [LabTestsController::class, 'store'])->name('lab-tests.store');
            Route::get('/{labTest}', [LabTestsController::class, 'show'])->name('lab-tests.show');
            Route::get('/{labTest}/edit', [LabTestsController::class, 'edit'])->name('lab-tests.edit');
            Route::put('/{labTest}', [LabTestsController::class, 'update'])->name('lab-tests.update');
            Route::delete('/{labTest}', [LabTestsController::class, 'destroy'])->name('lab-tests.destroy');
        });

    Route::prefix('prescriptions')
        ->middleware('role:super_admin|admin|doctor')
        ->group(function () {
            Route::get('/', [PrescriptionsController::class, 'index'])->name('prescriptions.index');
            Route::get('/create', [PrescriptionsController::class, 'create'])->name('prescriptions.create');
            Route::post('/', [PrescriptionsController::class, 'store'])->name('prescriptions.store');
            Route::get('/{prescription}', [PrescriptionsController::class, 'show'])->name('prescriptions.show');
            Route::get('/{prescription}/edit', [PrescriptionsController::class, 'edit'])->name('prescriptions.edit');
            Route::put('/{prescription}', [PrescriptionsController::class, 'update'])->name('prescriptions.update');
            Route::delete('/{prescription}', [PrescriptionsController::class, 'destroy'])->name('prescriptions.destroy');
        });

    Route::prefix('medical-records')
        ->middleware('role:super_admin|admin|doctor')
        ->group(function () {
            Route::get('/', [MedicalRecordsController::class, 'index'])->name('medical-records.index');
            Route::get('/create', [MedicalRecordsController::class, 'create'])->name('medical-records.create');
            Route::post('/', [MedicalRecordsController::class, 'store'])->name('medical-records.store');
            Route::get('/{medicalRecord}', [MedicalRecordsController::class, 'show'])->name('medical-records.show');
            Route::get('/{medicalRecord}/edit', [MedicalRecordsController::class, 'edit'])->name('medical-records.edit');
            Route::put('/{medicalRecord}', [MedicalRecordsController::class, 'update'])->name('medical-records.update');
            Route::delete('/{medicalRecord}', [MedicalRecordsController::class, 'destroy'])->name('medical-records.destroy');
        });

    Route::prefix('pharmacy')
        ->middleware('role:super_admin|admin|pharmacist')
        ->group(function () {
            Route::get('/medicines', [MedicinesController::class, 'index'])->name('medicines.index');
            Route::get('/medicines/create', [MedicinesController::class, 'create'])->name('medicines.create');
            Route::post('/medicines', [MedicinesController::class, 'store'])->name('medicines.store');
            Route::get('/medicines/{medicine}', [MedicinesController::class, 'show'])->name('medicines.show');
            Route::get('/medicines/{medicine}/edit', [MedicinesController::class, 'edit'])->name('medicines.edit');
            Route::put('/medicines/{medicine}', [MedicinesController::class, 'update'])->name('medicines.update');
            Route::delete('/medicines/{medicine}', [MedicinesController::class, 'destroy'])->name('medicines.destroy');
            Route::get('/dispense', [PharmacyDispensesController::class, 'index'])->name('pharmacy.dispense.index');
            Route::post('/dispense/{prescription}', [PharmacyDispensesController::class, 'store'])->name('pharmacy.dispense.store');
            Route::get('/ledger', [PharmacyLedgerController::class, 'index'])->name('pharmacy.ledger.index');
        });

    Route::prefix('billing')
        ->middleware('role:super_admin|admin|accountant')
        ->group(function () {
            Route::get('/', [WebBillingController::class, 'index'])->name('billing.index');
            Route::get('/create', [WebBillingController::class, 'create'])->name('billing.create');
            Route::post('/', [WebBillingController::class, 'store'])->name('billing.store');
            Route::get('/{billing}', [WebBillingController::class, 'show'])->name('billing.show');
            Route::put('/{billing}/pay', [WebBillingController::class, 'pay'])->name('billing.pay');
            Route::put('/{billing}/cancel', [WebBillingController::class, 'cancel'])->name('billing.cancel');
            Route::delete('/{billing}', [WebBillingController::class, 'destroy'])->name('billing.destroy');
        });

    Route::prefix('staff')
        ->middleware('role:super_admin|admin|hr_manager')
        ->group(function () {
            Route::get('/', [StaffController::class, 'index'])->name('staff.index');
            Route::get('/create', [StaffController::class, 'create'])->name('staff.create');
            Route::post('/', [StaffController::class, 'store'])->name('staff.store');
            Route::get('/{staff}', [StaffController::class, 'show'])->name('staff.show');
            Route::get('/{staff}/edit', [StaffController::class, 'edit'])->name('staff.edit');
            Route::put('/{staff}', [StaffController::class, 'update'])->name('staff.update');
            Route::delete('/{staff}', [StaffController::class, 'destroy'])->name('staff.destroy');
        });

    Route::prefix('shifts')
        ->middleware('role:super_admin|admin|hr_manager|doctor')
        ->group(function () {
            Route::get('/', [ShiftsController::class, 'index'])->name('shifts.index');
            Route::get('/assign', [ShiftsController::class, 'create'])->name('shifts.create');
            Route::post('/assign', [ShiftsController::class, 'store'])->name('shifts.store');
        });

    Route::prefix('wards-beds')
        ->middleware('role:super_admin|admin|nurse|doctor')
        ->group(function () {
            Route::get('/', [WardsBedsController::class, 'index'])->name('wards-beds.index');
            // Wards
            Route::get('/wards', [WardsBedsController::class, 'wardsIndex'])->name('wards.index');
            Route::get('/wards/create', [WardsBedsController::class, 'wardsCreate'])->name('wards.create');
            Route::post('/wards', [WardsBedsController::class, 'wardsStore'])->name('wards.store');
            Route::get('/wards/{ward}/edit', [WardsBedsController::class, 'wardsEdit'])->name('wards.edit');
            Route::put('/wards/{ward}', [WardsBedsController::class, 'wardsUpdate'])->name('wards.update');
            Route::delete('/wards/{ward}', [WardsBedsController::class, 'wardsDestroy'])->name('wards.destroy');

            // Beds
            Route::get('/beds', [WardsBedsController::class, 'bedsIndex'])->name('beds.index');
            Route::get('/beds/create', [WardsBedsController::class, 'bedsCreate'])->name('beds.create');
            Route::post('/beds', [WardsBedsController::class, 'bedsStore'])->name('beds.store');
            Route::get('/beds/{bed}/edit', [WardsBedsController::class, 'bedsEdit'])->name('beds.edit');
            Route::put('/beds/{bed}', [WardsBedsController::class, 'bedsUpdate'])->name('beds.update');
            Route::delete('/beds/{bed}', [WardsBedsController::class, 'bedsDestroy'])->name('beds.destroy');

            // Allocations
            Route::get('/allocations', [WardsBedsController::class, 'allocationsIndex'])->name('allocations.index');
            Route::get('/allocations/assign', [WardsBedsController::class, 'allocationsCreate'])->name('allocations.create');
            Route::post('/allocations/assign', [WardsBedsController::class, 'allocationsStore'])->name('allocations.store');
            Route::put('/allocations/{allocation}/release', [WardsBedsController::class, 'allocationsRelease'])->name('allocations.release');
            Route::put('/allocations/{allocation}/transfer', [WardsBedsController::class, 'allocationsTransfer'])->name('allocations.transfer');
        });

    Route::prefix('reports')
        ->middleware('role:super_admin|admin|doctor|nurse|receptionist|accountant|pharmacist|lab_technician|hr_manager')
        ->group(function () {
            Route::get('/', [WebReportsController::class, 'index'])->name('reports.index');
            Route::get('/patients', [WebReportsController::class, 'patients'])->name('reports.patients');
            Route::get('/appointments', [WebReportsController::class, 'appointments'])->name('reports.appointments');
            Route::get('/billing', [WebReportsController::class, 'billing'])->name('reports.billing');
            Route::get('/lab-tests', [WebReportsController::class, 'labTests'])->name('reports.lab-tests');
            Route::get('/pharmacy', [WebReportsController::class, 'pharmacy'])->name('reports.pharmacy');
            Route::get('/ward-bed', [WebReportsController::class, 'wardBed'])->name('reports.ward-bed');
            Route::get('/staff', [WebReportsController::class, 'staff'])->name('reports.staff');
        });

    Route::prefix('admin')
        ->middleware('role:super_admin|admin')
        ->group(function () {
            Route::get('/appointments', [AdminAppointmentsController::class, 'index'])->name('admin.appointments.index');
            Route::put('/appointments/{appointment}/status', [AdminAppointmentsController::class, 'updateStatus'])->name('admin.appointments.status');

            Route::get('/users', [AdminUsersController::class, 'index'])->name('admin.users.index');
            Route::get('/users/create', [AdminUsersController::class, 'create'])->name('admin.users.create');
            Route::post('/users', [AdminUsersController::class, 'store'])->name('admin.users.store');
            Route::put('/users/{user}/role', [AdminUsersController::class, 'assignRole'])->name('admin.users.role.assign');
            Route::delete('/users/{user}/role', [AdminUsersController::class, 'removeRole'])->name('admin.users.role.remove');
            Route::put('/users/{user}/department', [AdminUsersController::class, 'updateDepartment'])->name('admin.users.department.update');
        });

    Route::prefix('system')
        ->middleware('role:super_admin')
        ->group(function () {
            Route::get('/backups', [SystemBackupsController::class, 'index'])->name('system.backups.index');
            Route::post('/backups', [SystemBackupsController::class, 'store'])->name('system.backups.store');
            Route::get('/backups/{backup}/download', [SystemBackupsController::class, 'download'])->name('system.backups.download');

            Route::get('/logs/activity', [SystemLogsController::class, 'activity'])->name('system.logs.activity');
            Route::get('/logs/logins', [SystemLogsController::class, 'logins'])->name('system.logs.logins');
        });
});
