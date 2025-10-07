<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Superadministrador
        $superadmin = User::create([
            'type' => 'superadmin',
            'name' => 'Super Administrador',
            'email' => 'superadmin@netfacture.ec',
            'password' => Hash::make('superadmin123'),
            'phone' => '+593999999999',
            'is_active' => true,
        ]);

        // Owners de prueba
        $owner1 = User::create([
            'type' => 'owner',
            'name' => 'Juan Pérez',
            'email' => 'juan.perez@example.com',
            'password' => Hash::make('password123'),
            'phone' => '+593987654321',
            'is_active' => true,
        ]);

        $owner2 = User::create([
            'type' => 'owner',
            'name' => 'María González',
            'email' => 'maria.gonzalez@example.com',
            'password' => Hash::make('password123'),
            'phone' => '+593976543210',
            'is_active' => true,
        ]);

        $owner3 = User::create([
            'type' => 'owner',
            'name' => 'Carlos Mendoza',
            'email' => 'carlos.mendoza@example.com',
            'password' => Hash::make('password123'),
            'phone' => '+593965432109',
            'is_active' => true,
        ]);

        $this->command->info('✅ 4 usuarios creados:');
        $this->command->line('   🔴 Superadmin: superadmin@netfacture.ec / superadmin123');
        $this->command->line('   🟢 Owner 1: juan.perez@example.com / password123');
        $this->command->line('   🟢 Owner 2: maria.gonzalez@example.com / password123');
        $this->command->line('   🟢 Owner 3: carlos.mendoza@example.com / password123');
    }
}
