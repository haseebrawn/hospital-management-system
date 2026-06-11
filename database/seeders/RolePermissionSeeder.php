<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Department;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Departments
        $departments = [
            'Administration',
            'OPD',
            'Reception',
            'Laboratory',
            'Pharmacy',
            'Finance',
            'HR',
            'IT',
            'Wards'
        ];

        foreach ($departments as $d) {
            Department::firstOrCreate(['name' => $d]);
        }

        // Permissions
        $permissions = [
            'manage users',
            'manage roles',
            'manage departments',
            'view patients',
            'edit patients',
            'create appointments',
            'manage prescriptions',
            'manage lab tests',
            'manage billing',
            'manage medicines',
            'dispense prescriptions',
            'manage staff',
            'view staff',
            'assign shifts',
            'view shifts',
            'manage backups',
            'view backups',
            'view logs',
            'manage security',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'api']);
        }

        // Roles
        $roles = [
            'super_admin',
            'user',
            'admin',
            'doctor',
            'nurse',
            'receptionist',
            'lab_technician',
            'pharmacist',
            'accountant',
            'hr_manager',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'api']);
        }

        $permissionLikeRoles = [
            'view logs',
            'manage backups',
            'manage security',
            'view backups',
        ];

        Role::where('guard_name', 'api')
            ->whereIn('name', $permissionLikeRoles)
            ->get()
            ->each(function (Role $role) {
                $role->users()->detach();
                $role->permissions()->detach();
                $role->delete();
            });

        // Assign Permissions
        $superAdminRole = Role::findByName('super_admin');
        $superAdminRole->syncPermissions(Permission::all());

        // Lab Technicians get only lab related permissions
        $labTechRole = Role::findByName('lab_technician');
        $labTechRole->givePermissionTo(['manage lab tests']);

        $pharmacist = Role::where('name', 'pharmacist')->first();
        $pharmacist->syncPermissions([
            'manage medicines',
            'dispense prescriptions',
        ]);

        // HR Manager full HR rights
        $hrRole = Role::where('name', 'hr_manager')->first();
        $hrRole?->syncPermissions([
            'manage staff',
            'view staff',
            'assign shifts',
            'view shifts'
        ]);

        // Department Admin manages only their department workflow.
        $adminRole = Role::where('name', 'admin')->first();
        $adminRole?->syncPermissions([
            'manage users',
            'view patients',
            'edit patients',
            'create appointments',
            'manage prescriptions',
            'manage lab tests',
            'manage billing',
            'manage medicines',
            'dispense prescriptions',
            'manage staff',
            'view staff',
            'assign shifts',
            'view shifts',
        ]);

        // Doctor & HOD Department Admin only view staff & shift
        $doctorRole = Role::where('name', 'doctor')->first();
        $doctorRole?->givePermissionTo([
            'view patients',
            'edit patients',
            'manage prescriptions',
            'manage lab tests',
            'view staff',
            'view shifts'
        ]);

        // Reception handles patient registration and appointment scheduling
        $receptionistRole = Role::where('name', 'receptionist')->first();
        $receptionistRole?->syncPermissions([
            'view patients',
            'edit patients',
            'create appointments',
        ]);

        // Nurses support patient flow and ward/appointment coordination
        $nurseRole = Role::where('name', 'nurse')->first();
        $nurseRole?->syncPermissions([
            'view patients',
            'edit patients',
            'create appointments',
            'view shifts',
        ]);

        // Accountant owns billing operations
        $accountantRole = Role::where('name', 'accountant')->first();
        $accountantRole?->syncPermissions([
            'manage billing',
        ]);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
