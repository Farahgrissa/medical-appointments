<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;


class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // ===== Clear cached roles and permissions =====
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ===== Create permissions =====
        $permissions = [
            'create-appointment',
            'view-appointment',
            'edit-appointment',
            'delete-appointment',
            'view-patient-file',
            'edit-patient-file',
            'create-slot',
            'view-slot',
            'edit-slot',
            'delete-slot',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // ===== Create roles and assign permissions =====
        $roles = [
            'admin' => $permissions,
            'doctor' => [
                'create-appointment', 'view-appointment', 'edit-appointment', 'view-patient-file',
                'create-slot', 'view-slot', 'edit-slot', 'delete-slot','edit-profile','delete-profile'
            ],
            'patient' => ['view-appointment', 'view-patient-file','edit-appointment','edit-profile','delete-profile','view-profile'],
        ];

        foreach ($roles as $roleName => $rolePerms) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePerms);
        }
    }
}
