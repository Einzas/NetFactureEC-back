<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User permissions
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            
            // Role permissions
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',
            
            // Permission permissions
            'permissions.view',
            'permissions.create',
            'permissions.edit',
            'permissions.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        
        // Superadmin - all permissions
        $superadmin = Role::firstOrCreate(['name' => 'superadmin']);
        $superadmin->givePermissionTo(Permission::all());

        // Admin - most permissions except superadmin exclusive
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo([
            'users.view',
            'users.create',
            'users.edit',
            'roles.view',
            'permissions.view',
        ]);

        // User - basic permissions
        $user = Role::firstOrCreate(['name' => 'user']);
        // Users have no special permissions by default
        
        $this->command->info('Roles and permissions seeded successfully!');
    }
}

