<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\LabTestController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\HRController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\WardController;
use App\Http\Controllers\BedController;
use App\Http\Controllers\BedAllocationController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\AuditController;

Route::prefix('v1')->group(function () {

    // Public Authentication Routes
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
        // 5 attempts per 1 minute
    });

    // Protected Routes
    Route::middleware(['auth', 'api'])->group(function () {

        Route::get('/profile', [AuthController::class, 'profile']);
        Route::post('/logout', [AuthController::class, 'logout']);

        // Notifications
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::post('/read-all', [NotificationController::class, 'markAllRead']);
        });

        // Admin Routes
        Route::prefix('admin')->middleware('role:super_admin|admin')->group(function () {
            Route::get('/users', [UserManagementController::class, 'index']);
            Route::put('/users/{id}/role', [UserManagementController::class, 'assignRole'])
                ->middleware(\App\Http\Middleware\EnsureDepartmentAdmin::class);
            Route::delete('/users/{id}/role', [UserManagementController::class, 'removeRole'])
                ->middleware(\App\Http\Middleware\EnsureDepartmentAdmin::class);
            Route::put('/users/{id}/department', [UserManagementController::class, 'updateDepartment'])
                ->middleware('role:super_admin');
        });

        // Patients Module
        Route::prefix('patients')->middleware(['role:super_admin|doctor|nurse|receptionist'])->group(function () {
            Route::get('/', [PatientController::class, 'index']);
            Route::post('/', [PatientController::class, 'store']);
            Route::get('/{id}', [PatientController::class, 'show']);
            Route::put('/{id}', [PatientController::class, 'update']);
            Route::delete('/{id}', [PatientController::class, 'destroy']);
        });

        // Appointment Module
        Route::prefix('appointments')->group(function () {
            Route::post('/', [AppointmentController::class, 'store'])
                ->middleware('role:super_admin|admin|receptionist');
            Route::get('/', [AppointmentController::class, 'index'])
                ->middleware('role:super_admin|admin|doctor|receptionist');
            Route::put('/{id}', [AppointmentController::class, 'update'])
                ->middleware('role:super_admin|admin|doctor');
            Route::delete('/{id}', [AppointmentController::class, 'destroy'])
                ->middleware('role:super_admin|admin');
        });

        //  Lab Test Module
        Route::prefix('lab-tests')->group(function () {
            Route::get('/', [LabTestController::class, 'index'])
                ->middleware('role:lab_technician|doctor');
            Route::post('/', [LabTestController::class, 'store'])
                ->middleware('role:lab_technician');
            Route::put('/{id}', [LabTestController::class, 'update'])
                ->middleware('role:lab_technician');
            Route::delete('/{id}', [LabTestController::class, 'destroy'])
                ->middleware('role:lab_technician');
        });

        // Pharmacy Module
        Route::prefix('pharmacy')->middleware('role:super_admin|pharmacist')->group(function () {

            // Medicine CRUD
            Route::get('/medicines', [MedicineController::class, 'index']);
            Route::post('/medicines', [MedicineController::class, 'store']);
            Route::get('/medicines/{id}', [MedicineController::class, 'show']);
            Route::put('/medicines/{id}', [MedicineController::class, 'update']);
            Route::delete('/medicines/{id}', [MedicineController::class, 'destroy']);

            // Prescription Handling
            Route::get('/prescriptions', [PharmacyController::class, 'pendingPrescriptions']); // Only pending
            Route::put('/prescriptions/{id}/dispense', [PharmacyController::class, 'dispense']);
        });


        // Doctor Can View Medicine List To Prescribe
        Route::get('/pharmacy/medicines', [MedicineController::class, 'index'])
            ->middleware('role:doctor|super_admin|pharmacist');

        // Billing System
        Route::prefix('billing')->middleware(['role:super_admin|accountant'])->group(function () {
            Route::post('/', [BillingController::class, 'createInvoice']);
            Route::get('/', [BillingController::class, 'index']);
            Route::get('/{id}', [BillingController::class, 'show']);
            Route::put('/{id}/pay', [BillingController::class, 'markAsPaid']);
            Route::put('/{id}/cancel', [BillingController::class, 'cancelInvoice']);
        });

        // Staff (HR + Admin full control, Doctors view only)
        Route::prefix('staff')->group(function () {
            Route::get('/', [HRController::class, 'index'])->middleware('permission:view staff|manage staff');
            Route::post('/', [HRController::class, 'store'])->middleware('permission:manage staff');
            Route::put('/{id}', [HRController::class, 'update'])->middleware('permission:manage staff');
            Route::delete('/{id}', [HRController::class, 'destroy'])->middleware('permission:manage staff');
        });

        // Shift Management
        Route::prefix('shifts')->group(function () {
            Route::get('/', [ShiftController::class, 'index'])->middleware('permission:view shifts|assign shifts');
            Route::post('/assign', [ShiftController::class, 'assign'])->middleware('permission:assign shifts');
        });

        Route::middleware('role:super_admin|admin|nurse|doctor')->group(function () {

            // Wards
            Route::get('/wards', [WardController::class, 'index']);
            Route::post('/wards', [WardController::class, 'store'])->middleware('role:super_admin|admin');
            Route::put('/wards/{id}', [WardController::class, 'update'])->middleware('role:super_admin|admin');
            Route::delete('/wards/{id}', [WardController::class, 'destroy'])->middleware('role:super_admin|admin');

            // Beds
            Route::get('/beds', [BedController::class, 'index']);
            Route::post('/beds', [BedController::class, 'store'])->middleware('role:super_admin|admin');
            Route::put('/beds/{id}', [BedController::class, 'update'])->middleware('role:super_admin|admin|nurse');
            Route::delete('/beds/{id}', [BedController::class, 'destroy'])->middleware('role:super_admin|admin');

            // Bed Allocation
            Route::post('/beds/assign', [BedAllocationController::class, 'assign'])->middleware('role:super_admin|admin|nurse');
            Route::put('/beds/allocation/{id}/release', [BedAllocationController::class, 'release'])->middleware('role:super_admin|admin|nurse');
            Route::put('/beds/allocation/{id}/transfer', [BedAllocationController::class, 'transfer'])->middleware('role:super_admin|admin|nurse');
        });

        Route::prefix('reports')->group(function () {
            Route::get('/patients', [ReportsController::class, 'patientReport'])
                ->middleware('role:super_admin|admin|doctor|nurse');
            Route::get('/appointments', [ReportsController::class, 'appointmentReport'])
                ->middleware('role:super_admin|admin|doctor');
            Route::get('/billing', [ReportsController::class, 'billingReport'])
                ->middleware('role:super_admin|admin|accountant');
            Route::get('/pharmacy', [ReportsController::class, 'pharmacyReport'])
                ->middleware('role:super_admin|admin|pharmacist');
            Route::get('/lab-tests', [ReportsController::class, 'labReport'])
                ->middleware('role:super_admin|admin|lab_technician|doctor');
            Route::get('/ward-bed', [ReportsController::class, 'wardBedReport'])
                ->middleware('role:super_admin|admin|nurse');
            Route::get('/staff', [ReportsController::class, 'staffReport'])
                ->middleware('role:super_admin|admin|hr_manager');
        });
    });

    Route::middleware(['auth:sanctum', 'role:super_admin|admin|accountant'])->group(function () {
        Route::get('/admin/backups', [BackupController::class, 'index']);
        Route::post('/admin/backups', [BackupController::class, 'create'])->middleware('permission:manage backups');
        Route::get('/admin/backups/{id}/download', [BackupController::class, 'download'])->middleware('permission:manage backups');
    });

    Route::middleware(['auth:sanctum', 'role:super_admin|admin|hr_manager'])->group(function () {
        Route::get('/admin/logs/activity', [AuditController::class, 'activity']);
        Route::get('/admin/logs/logins', [AuditController::class, 'logins']);
    });
});
