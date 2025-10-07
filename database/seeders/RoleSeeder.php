<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Roles del sistema (globales, no pertenecen a ninguna empresa)
        
        // 1. Administrador Total (para employees que tienen acceso completo)
        $adminRole = Role::create([
            'company_id' => null,
            'name' => 'admin',
            'display_name' => 'Administrador Total',
            'description' => 'Acceso completo a todos los módulos de la empresa',
            'is_system' => true,
        ]);
        
        // Asignar todos los permisos excepto los de superadmin
        $adminPermissions = Permission::whereNotIn('module', ['users'])->pluck('id');
        $adminRole->permissions()->attach($adminPermissions);

        // 2. Contador
        $accountantRole = Role::create([
            'company_id' => null,
            'name' => 'accountant',
            'display_name' => 'Contador',
            'description' => 'Gestión de facturas, notas de crédito, retenciones y reportes',
            'is_system' => true,
        ]);
        
        $accountantPermissions = Permission::whereIn('name', [
            'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.authorize',
            'credit-notes.view', 'credit-notes.create', 'credit-notes.authorize',
            'withholdings.view', 'withholdings.create', 'withholdings.authorize',
            'reports.view', 'reports.export', 'reports.analytics',
            'files.view', 'files.upload', 'files.download',
        ])->pluck('id');
        $accountantRole->permissions()->attach($accountantPermissions);

        // 3. Facturador
        $billerRole = Role::create([
            'company_id' => null,
            'name' => 'biller',
            'display_name' => 'Facturador',
            'description' => 'Creación y gestión de facturas',
            'is_system' => true,
        ]);
        
        $billerPermissions = Permission::whereIn('name', [
            'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.authorize',
            'files.view', 'files.upload', 'files.download',
            'reports.view',
        ])->pluck('id');
        $billerRole->permissions()->attach($billerPermissions);

        // 4. Vendedor
        $salesRole = Role::create([
            'company_id' => null,
            'name' => 'sales',
            'display_name' => 'Vendedor',
            'description' => 'Creación de facturas básicas',
            'is_system' => true,
        ]);
        
        $salesPermissions = Permission::whereIn('name', [
            'invoices.view', 'invoices.create',
            'files.view',
        ])->pluck('id');
        $salesRole->permissions()->attach($salesPermissions);

        // 5. Auditor (solo lectura)
        $auditorRole = Role::create([
            'company_id' => null,
            'name' => 'auditor',
            'display_name' => 'Auditor',
            'description' => 'Acceso de solo lectura a todos los módulos',
            'is_system' => true,
        ]);
        
        $auditorPermissions = Permission::where('name', 'like', '%.view')
            ->whereNotIn('module', ['users'])
            ->pluck('id');
        $auditorRole->permissions()->attach($auditorPermissions);

        // 6. Asistente Administrativo
        $assistantRole = Role::create([
            'company_id' => null,
            'name' => 'assistant',
            'display_name' => 'Asistente Administrativo',
            'description' => 'Gestión de archivos y reportes básicos',
            'is_system' => true,
        ]);
        
        $assistantPermissions = Permission::whereIn('name', [
            'files.view', 'files.upload', 'files.download',
            'reports.view',
            'invoices.view',
        ])->pluck('id');
        $assistantRole->permissions()->attach($assistantPermissions);

        $this->command->info('✅ 6 roles del sistema creados exitosamente:');
        $this->command->line('   - Administrador Total (todos los permisos)');
        $this->command->line('   - Contador (facturas, retenciones, reportes)');
        $this->command->line('   - Facturador (creación de facturas)');
        $this->command->line('   - Vendedor (facturas básicas)');
        $this->command->line('   - Auditor (solo lectura)');
        $this->command->line('   - Asistente Administrativo (archivos y reportes)');
    }
}
