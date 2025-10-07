<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Módulo: Usuarios (solo para superadmin)
            ['name' => 'users.view', 'display_name' => 'Ver Usuarios', 'description' => 'Ver listado de usuarios owners', 'module' => 'users'],
            ['name' => 'users.create', 'display_name' => 'Crear Usuarios', 'description' => 'Crear nuevos usuarios owners', 'module' => 'users'],
            ['name' => 'users.edit', 'display_name' => 'Editar Usuarios', 'description' => 'Editar usuarios owners', 'module' => 'users'],
            ['name' => 'users.delete', 'display_name' => 'Eliminar Usuarios', 'description' => 'Eliminar usuarios owners', 'module' => 'users'],
            
            // Módulo: Empresas (superadmin y owners)
            ['name' => 'companies.view', 'display_name' => 'Ver Empresas', 'description' => 'Ver empresas', 'module' => 'companies'],
            ['name' => 'companies.create', 'display_name' => 'Crear Empresas', 'description' => 'Crear nuevas empresas', 'module' => 'companies'],
            ['name' => 'companies.edit', 'display_name' => 'Editar Empresas', 'description' => 'Editar empresas', 'module' => 'companies'],
            ['name' => 'companies.delete', 'display_name' => 'Eliminar Empresas', 'description' => 'Eliminar empresas', 'module' => 'companies'],
            ['name' => 'companies.settings', 'display_name' => 'Configurar Empresas', 'description' => 'Cambiar configuración de empresas', 'module' => 'companies'],
            
            // Módulo: Empleados
            ['name' => 'employees.view', 'display_name' => 'Ver Empleados', 'description' => 'Ver listado de empleados', 'module' => 'employees'],
            ['name' => 'employees.create', 'display_name' => 'Crear Empleados', 'description' => 'Crear nuevos empleados', 'module' => 'employees'],
            ['name' => 'employees.edit', 'display_name' => 'Editar Empleados', 'description' => 'Editar empleados', 'module' => 'employees'],
            ['name' => 'employees.delete', 'display_name' => 'Eliminar Empleados', 'description' => 'Eliminar empleados', 'module' => 'employees'],
            ['name' => 'employees.manage-roles', 'display_name' => 'Gestionar Roles', 'description' => 'Asignar y remover roles a empleados', 'module' => 'employees'],
            ['name' => 'employees.manage-permissions', 'display_name' => 'Gestionar Permisos', 'description' => 'Asignar permisos directos a empleados', 'module' => 'employees'],
            
            // Módulo: Roles
            ['name' => 'roles.view', 'display_name' => 'Ver Roles', 'description' => 'Ver roles de la empresa', 'module' => 'roles'],
            ['name' => 'roles.create', 'display_name' => 'Crear Roles', 'description' => 'Crear roles personalizados', 'module' => 'roles'],
            ['name' => 'roles.edit', 'display_name' => 'Editar Roles', 'description' => 'Editar roles personalizados', 'module' => 'roles'],
            ['name' => 'roles.delete', 'display_name' => 'Eliminar Roles', 'description' => 'Eliminar roles personalizados', 'module' => 'roles'],
            
            // Módulo: Archivos
            ['name' => 'files.view', 'display_name' => 'Ver Archivos', 'description' => 'Ver listado de archivos', 'module' => 'files'],
            ['name' => 'files.upload', 'display_name' => 'Subir Archivos', 'description' => 'Subir archivos', 'module' => 'files'],
            ['name' => 'files.download', 'display_name' => 'Descargar Archivos', 'description' => 'Descargar archivos', 'module' => 'files'],
            ['name' => 'files.delete', 'display_name' => 'Eliminar Archivos', 'description' => 'Eliminar archivos', 'module' => 'files'],
            ['name' => 'files.manage', 'display_name' => 'Gestionar Archivos', 'description' => 'Administrar archivos de otros usuarios', 'module' => 'files'],
            
            // Módulo: Facturas
            ['name' => 'invoices.view', 'display_name' => 'Ver Facturas', 'description' => 'Ver facturas', 'module' => 'invoices'],
            ['name' => 'invoices.create', 'display_name' => 'Crear Facturas', 'description' => 'Crear nuevas facturas', 'module' => 'invoices'],
            ['name' => 'invoices.edit', 'display_name' => 'Editar Facturas', 'description' => 'Editar facturas', 'module' => 'invoices'],
            ['name' => 'invoices.delete', 'display_name' => 'Eliminar Facturas', 'description' => 'Eliminar facturas', 'module' => 'invoices'],
            ['name' => 'invoices.authorize', 'display_name' => 'Autorizar Facturas', 'description' => 'Enviar facturas al SRI', 'module' => 'invoices'],
            ['name' => 'invoices.cancel', 'display_name' => 'Anular Facturas', 'description' => 'Anular facturas autorizadas', 'module' => 'invoices'],
            
            // Módulo: Notas de Crédito
            ['name' => 'credit-notes.view', 'display_name' => 'Ver Notas de Crédito', 'description' => 'Ver notas de crédito', 'module' => 'credit-notes'],
            ['name' => 'credit-notes.create', 'display_name' => 'Crear Notas de Crédito', 'description' => 'Crear notas de crédito', 'module' => 'credit-notes'],
            ['name' => 'credit-notes.authorize', 'display_name' => 'Autorizar Notas de Crédito', 'description' => 'Enviar notas de crédito al SRI', 'module' => 'credit-notes'],
            
            // Módulo: Retenciones
            ['name' => 'withholdings.view', 'display_name' => 'Ver Retenciones', 'description' => 'Ver retenciones', 'module' => 'withholdings'],
            ['name' => 'withholdings.create', 'display_name' => 'Crear Retenciones', 'description' => 'Crear retenciones', 'module' => 'withholdings'],
            ['name' => 'withholdings.authorize', 'display_name' => 'Autorizar Retenciones', 'description' => 'Enviar retenciones al SRI', 'module' => 'withholdings'],
            
            // Módulo: Reportes
            ['name' => 'reports.view', 'display_name' => 'Ver Reportes', 'description' => 'Ver reportes', 'module' => 'reports'],
            ['name' => 'reports.export', 'display_name' => 'Exportar Reportes', 'description' => 'Exportar reportes a Excel/PDF', 'module' => 'reports'],
            ['name' => 'reports.analytics', 'display_name' => 'Ver Analytics', 'description' => 'Ver dashboard analítico', 'module' => 'reports'],
            
            // Módulo: Configuración
            ['name' => 'settings.view', 'display_name' => 'Ver Configuración', 'description' => 'Ver configuración del sistema', 'module' => 'settings'],
            ['name' => 'settings.edit', 'display_name' => 'Editar Configuración', 'description' => 'Modificar configuración', 'module' => 'settings'],
            ['name' => 'settings.sri', 'display_name' => 'Configurar SRI', 'description' => 'Configurar firma electrónica y SRI', 'module' => 'settings'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        $this->command->info('✅ ' . count($permissions) . ' permisos creados exitosamente');
    }
}
