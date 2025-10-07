<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('🌱 ════════════════════════════════════════════════════════════════');
        $this->command->info('🌱 INICIANDO SEED DEL SISTEMA PROFESIONAL MULTI-TENANT');
        $this->command->info('🌱 ════════════════════════════════════════════════════════════════');
        $this->command->info('');

        // 1. Permisos (base del RBAC)
        $this->command->info('📋 Paso 1/5: Creando permisos...');
        $this->call(PermissionSeeder::class);
        $this->command->info('');

        // 2. Roles del sistema
        $this->command->info('🎭 Paso 2/5: Creando roles del sistema...');
        $this->call(RoleSeeder::class);
        $this->command->info('');

        // 3. Usuarios (superadmin + owners)
        $this->command->info('👥 Paso 3/5: Creando usuarios...');
        $this->call(UserSeeder::class);
        $this->command->info('');

        // 4. Empresas
        $this->command->info('🏢 Paso 4/5: Creando empresas...');
        $this->call(CompanySeeder::class);
        $this->command->info('');

        // 5. Empleados con roles
        $this->command->info('👤 Paso 5/5: Creando empleados...');
        $this->call(EmployeeSeeder::class);
        $this->command->info('');

        $this->command->info('🌱 ════════════════════════════════════════════════════════════════');
        $this->command->info('✅ SEED COMPLETADO EXITOSAMENTE');
        $this->command->info('🌱 ════════════════════════════════════════════════════════════════');
        $this->command->info('');
        $this->command->info('📊 RESUMEN DEL SISTEMA:');
        $this->command->info('   • 44 Permisos granulares');
        $this->command->info('   • 6 Roles del sistema');
        $this->command->info('   • 1 Superadmin + 3 Owners');
        $this->command->info('   • 4 Empresas activas');
        $this->command->info('   • 10 Empleados con roles asignados');
        $this->command->info('');
        $this->command->info('🔐 CREDENCIALES DE ACCESO:');
        $this->command->info('');
        $this->command->info('   SUPERADMIN:');
        $this->command->info('   📧 superadmin@netfacture.ec');
        $this->command->info('   🔑 superadmin123');
        $this->command->info('');
        $this->command->info('   OWNERS:');
        $this->command->info('   📧 juan.perez@example.com (3 empleados)');
        $this->command->info('   📧 maria.gonzalez@example.com (2 empresas, 3 empleados)');
        $this->command->info('   📧 carlos.mendoza@example.com (4 empleados)');
        $this->command->info('   🔑 password123 (todos los owners)');
        $this->command->info('');
        $this->command->info('   EMPLEADOS (ejemplos):');
        $this->command->info('   📧 admin.tec@tecsoluciones.com 🔑 admin123');
        $this->command->info('   📧 contador.tec@tecsoluciones.com 🔑 contador123');
        $this->command->info('   📧 admin.mendoza@mendozaconsultores.com 🔑 admin123');
        $this->command->info('');
        $this->command->info('🌱 ════════════════════════════════════════════════════════════════');
        $this->command->info('');
    }
}

