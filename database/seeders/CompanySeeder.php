<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $owner1 = User::where('email', 'juan.perez@example.com')->first();
        $owner2 = User::where('email', 'maria.gonzalez@example.com')->first();
        $owner3 = User::where('email', 'carlos.mendoza@example.com')->first();

        // Empresa 1 - Juan Pérez
        Company::create([
            'owner_id' => $owner1->id,
            'ruc' => '1790123456001',
            'business_name' => 'TECNOLOGÍA Y SOLUCIONES TEC S.A.',
            'trade_name' => 'TecSoluciones',
            'address' => 'Av. Amazonas N24-03 y Wilson',
            'city' => 'Quito',
            'province' => 'Pichincha',
            'phone' => '+593987654321',
            'email' => 'info@tecsoluciones.com',
            'is_active' => true,
        ]);

        // Empresa 2 - María González (2 empresas)
        Company::create([
            'owner_id' => $owner2->id,
            'ruc' => '0992345678001',
            'business_name' => 'COMERCIALIZADORA GONZALEZ CIA. LTDA.',
            'trade_name' => 'Comercial González',
            'address' => 'Av. 6 de Diciembre N34-120 y Portugal',
            'city' => 'Quito',
            'province' => 'Pichincha',
            'phone' => '+593976543210',
            'email' => 'ventas@comercialgonzalez.com',
            'is_active' => true,
        ]);

        Company::create([
            'owner_id' => $owner2->id,
            'ruc' => '0992345678002',
            'business_name' => 'DISTRIBUIDORA COSTA S.A.',
            'trade_name' => 'Distribuidora Costa',
            'address' => 'Av. Malecón y 9 de Octubre',
            'city' => 'Guayaquil',
            'province' => 'Guayas',
            'phone' => '+593976543211',
            'email' => 'info@districosta.com',
            'is_active' => true,
        ]);

        // Empresa 3 - Carlos Mendoza
        Company::create([
            'owner_id' => $owner3->id,
            'ruc' => '1891234567001',
            'business_name' => 'SERVICIOS PROFESIONALES MENDOZA & ASOCIADOS',
            'trade_name' => 'Mendoza Consultores',
            'address' => 'Av. República del Salvador N34-123',
            'city' => 'Quito',
            'province' => 'Pichincha',
            'phone' => '+593965432109',
            'email' => 'contacto@mendozaconsultores.com',
            'is_active' => true,
        ]);

        $this->command->info('✅ 4 empresas creadas:');
        $this->command->line('   📊 TecSoluciones (Owner: Juan Pérez)');
        $this->command->line('   📊 Comercial González (Owner: María González)');
        $this->command->line('   📊 Distribuidora Costa (Owner: María González)');
        $this->command->line('   📊 Mendoza Consultores (Owner: Carlos Mendoza)');
    }
}
