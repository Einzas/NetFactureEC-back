<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tabla de permisos
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., 'files.create', 'users.delete'
            $table->string('display_name'); // e.g., 'Crear Archivos'
            $table->string('description')->nullable();
            $table->string('module'); // e.g., 'files', 'users', 'reports'
            $table->timestamps();
            
            $table->index(['name', 'module']);
        });

        // Tabla de roles
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
            $table->string('name'); // e.g., 'admin', 'manager', 'employee'
            $table->string('display_name'); // e.g., 'Administrador'
            $table->string('description')->nullable();
            $table->boolean('is_system')->default(false)->comment('Rol del sistema, no editable');
            $table->timestamps();
            
            // Un rol puede ser global (company_id null) o específico de empresa
            $table->unique(['company_id', 'name'], 'role_company_unique');
            $table->index('company_id');
        });

        // Tabla pivot: permisos <-> roles
        Schema::create('permission_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['permission_id', 'role_id']);
        });

        // Tabla pivot: roles <-> empleados
        Schema::create('employee_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['employee_id', 'role_id']);
        });

        // Permisos directos a empleados (opcional, para casos especiales)
        Schema::create('employee_permission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
            $table->boolean('granted')->default(true)->comment('true = conceder, false = revocar');
            $table->timestamps();
            
            $table->unique(['employee_id', 'permission_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_permission');
        Schema::dropIfExists('employee_role');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
    }
};
