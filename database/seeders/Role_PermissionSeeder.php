<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Role_PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define permissions
        $permissions = [
            'manage users',
            'create users',
            'edit users',
            'delete users',
            'view users',
            'manage reservations',
            'create reservations',
            'edit reservations',
            'delete reservations',
            'view reservations',
            'manage rooms',
            'create rooms',
            'edit rooms',
            'delete rooms',
            'view rooms',
            'manage clients',
            'view clients',
            'approve clients',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Define roles
        $roles = [
            'admin' => ['manage users', 'manage reservations', 'manage rooms', 'manage clients'],
            'manager' => ['view users', 'manage reservations', 'view rooms', 'approve clients'],
            'receptionist' => ['create reservations', 'edit reservations', 'delete reservations', 'view clients'],
            'client' => ['view reservations','create reservations'],
        ];

        // Assign permissions to roles
        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
        ]);

            foreach ($rolePermissions as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission) {
                    $role->givePermissionTo($permission);
                }
            }
        }
    }
}
