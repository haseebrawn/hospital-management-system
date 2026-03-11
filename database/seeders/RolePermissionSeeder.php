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
            'view logs',
            'manage backups',
            'manage security',
            'view backups'
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'api']);
        }

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

        // Admin same as HR Manager
        $adminRole = Role::where('name', 'admin')->first();
        $adminRole?->givePermissionTo([
            'manage staff',
            'view staff',
            'assign shifts',
            'view shifts'
        ]);

        // Doctor & HOD Department Admin only view staff & shift
        $doctorRole = Role::where('name', 'doctor')->first();
        $doctorRole?->givePermissionTo([
            'view staff',
            'view shifts'
        ]);


        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
