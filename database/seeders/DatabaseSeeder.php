<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions first
        $this->call(RolePermissionSeeder::class);

        // Create superadmin user
        $superadmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@netfactureec.com',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $superadmin->assignRole('superadmin');

        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@netfactureec.com',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        // Create regular user
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user@netfactureec.com',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $user->assignRole('user');

        $this->command->info('Database seeded successfully!');
    }
}

