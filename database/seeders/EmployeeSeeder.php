<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::all();
        
        // Empresa 1: TecSoluciones (3 empleados)
        $company1 = $companies->where('trade_name', 'TecSoluciones')->first();
        
        $emp1 = Employee::create([
            'company_id' => $company1->id,
            'email' => 'admin.tec@tecsoluciones.com',
            'password' => Hash::make('admin123'),
            'name' => 'Ana Martínez',
            'identification' => '1712345678001',
            'phone' => '+593991234567',
            'is_active' => true,
        ]);
        $emp1->assignRole('admin');

        $emp2 = Employee::create([
            'company_id' => $company1->id,
            'email' => 'contador.tec@tecsoluciones.com',
            'password' => Hash::make('contador123'),
            'name' => 'Roberto Sánchez',
            'identification' => '1723456789001',
            'phone' => '+593991234568',
            'is_active' => true,
        ]);
        $emp2->assignRole('accountant');

        $emp3 = Employee::create([
            'company_id' => $company1->id,
            'email' => 'ventas1.tec@tecsoluciones.com',
            'password' => Hash::make('ventas123'),
            'name' => 'Laura Torres',
            'identification' => '1734567890001',
            'phone' => '+593991234569',
            'is_active' => true,
        ]);
        $emp3->assignRole('sales');

        // Empresa 2: Comercial González (2 empleados)
        $company2 = $companies->where('trade_name', 'Comercial González')->first();
        
        $emp4 = Employee::create([
            'company_id' => $company2->id,
            'email' => 'admin.gonzalez@comercialgonzalez.com',
            'password' => Hash::make('admin123'),
            'name' => 'Pedro Ramírez',
            'identification' => '0912345678001',
            'phone' => '+593991234570',
            'is_active' => true,
        ]);
        $emp4->assignRole('admin');

        $emp5 = Employee::create([
            'company_id' => $company2->id,
            'email' => 'facturacion@comercialgonzalez.com',
            'password' => Hash::make('factura123'),
            'name' => 'Sofía Vargas',
            'identification' => '0923456789001',
            'phone' => '+593991234571',
            'is_active' => true,
        ]);
        $emp5->assignRole('biller');

        // Empresa 3: Distribuidora Costa (1 empleado)
        $company3 = $companies->where('trade_name', 'Distribuidora Costa')->first();
        
        $emp6 = Employee::create([
            'company_id' => $company3->id,
            'email' => 'supervisor@districosta.com',
            'password' => Hash::make('super123'),
            'name' => 'Diego Morales',
            'identification' => '0934567890001',
            'phone' => '+593991234572',
            'is_active' => true,
        ]);
        $emp6->assignRole('assistant');

        // Empresa 4: Mendoza Consultores (6 empleados)
        $company4 = $companies->where('trade_name', 'Mendoza Consultores')->first();
        
        $emp7 = Employee::create([
            'company_id' => $company4->id,
            'email' => 'admin.mendoza@mendozaconsultores.com',
            'password' => Hash::make('admin123'),
            'name' => 'Patricia Herrera',
            'identification' => '1745678901001',
            'phone' => '+593991234573',
            'is_active' => true,
        ]);
        $emp7->assignRole('admin');

        $emp8 = Employee::create([
            'company_id' => $company4->id,
            'email' => 'contador@mendozaconsultores.com',
            'password' => Hash::make('contador123'),
            'name' => 'Fernando López',
            'identification' => '1756789012001',
            'phone' => '+593991234574',
            'is_active' => true,
        ]);
        $emp8->assignRole('accountant');

        $emp9 = Employee::create([
            'company_id' => $company4->id,
            'email' => 'facturacion@mendozaconsultores.com',
            'password' => Hash::make('factura123'),
            'name' => 'Gabriela Ruiz',
            'identification' => '1767890123001',
            'phone' => '+593991234575',
            'is_active' => true,
        ]);
        $emp9->assignRole('biller');

        $emp10 = Employee::create([
            'company_id' => $company4->id,
            'email' => 'ventas1@mendozaconsultores.com',
            'password' => Hash::make('ventas123'),
            'name' => 'Miguel Castillo',
            'identification' => '1778901234001',
            'phone' => '+593991234576',
            'is_active' => true,
        ]);
        $emp10->assignRole('sales');

        $emp11 = Employee::create([
            'company_id' => $company4->id,
            'email' => 'asistente@mendozaconsultores.com',
            'password' => Hash::make('asistente123'),
            'name' => 'Carmen Flores',
            'identification' => '1789012345001',
            'phone' => '+593991234577',
            'is_active' => true,
        ]);
        $emp11->assignRole('assistant');

        $emp12 = Employee::create([
            'company_id' => $company4->id,
            'email' => 'consultas@mendozaconsultores.com',
            'password' => Hash::make('consultas123'),
            'name' => 'Ricardo Mendez',
            'identification' => '1790123456901',
            'phone' => '+593991234578',
            'is_active' => true,
        ]);
        $emp12->assignRole('viewer');

        $this->command->info('✅ 12 empleados creados distribuidos en 4 empresas');
    }
}
