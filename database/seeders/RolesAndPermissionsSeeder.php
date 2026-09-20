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
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'manage_users',
            'manage_books',
            'manage_categories',
            'manage_orders',
            'manage_payments',
            'view_dashboard',
            'soft_delete_books'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign created permissions
        $staffRole = Role::firstOrCreate(['name' => 'staff']);
        $staffRole->syncPermissions(['manage_books', 'manage_categories', 'manage_orders', 'view_dashboard']);

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions(Permission::all());

        // Assign existing users to roles based on the old 'role' string column
        $users = \App\Models\User::all();
        foreach ($users as $user) {
            if ($user->role === 'admin') {
                $user->assignRole('admin');
            } elseif ($user->role === 'staff') {
                $user->assignRole('staff');
            }
        }
    }
}
