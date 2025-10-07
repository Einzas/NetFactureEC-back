<?php

use App\Http\Controllers\Auth\SuperAdminAuthController;
use App\Http\Controllers\Auth\OwnerAuthController;
use App\Http\Controllers\Auth\EmployeeAuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Sistema Profesional Multi-Tenant con RBAC
|--------------------------------------------------------------------------
|
| Arquitectura de tres niveles:
| 1. SUPERADMIN - Dashboard analytics, gestión de owners
| 2. OWNER - Gestión de empresas y empleados
| 3. EMPLOYEE - Trabajo en empresa con permisos RBAC
|
*/

// ═══════════════════════════════════════════════════════════════════════════
// 🔴 SUPERADMIN - Panel de Control Global
// ═══════════════════════════════════════════════════════════════════════════

Route::prefix('superadmin')->group(function () {
    
    // Autenticación
    Route::post('login', [SuperAdminAuthController::class, 'login']);
    
    // Rutas protegidas
    Route::middleware(['auth:superadmin', 'auth.superadmin'])->group(function () {
        Route::get('me', [SuperAdminAuthController::class, 'me']);
        Route::post('logout', [SuperAdminAuthController::class, 'logout']);
        Route::post('refresh', [SuperAdminAuthController::class, 'refresh']);
        Route::get('dashboard', [SuperAdminAuthController::class, 'dashboard']);
        
        // TODO: Agregar gestión de owners, analytics, reportes globales
    });
});

// ═══════════════════════════════════════════════════════════════════════════
// 🟢 OWNER - Panel de Gestión de Empresas
// ═══════════════════════════════════════════════════════════════════════════

Route::prefix('owner')->group(function () {
    
    // Autenticación
    Route::post('login', [OwnerAuthController::class, 'login']);
    
    // Rutas protegidas
    Route::middleware(['auth:owner', 'auth.owner'])->group(function () {
        Route::get('me', [OwnerAuthController::class, 'me']);
        Route::post('logout', [OwnerAuthController::class, 'logout']);
        Route::post('refresh', [OwnerAuthController::class, 'refresh']);
        Route::get('dashboard', [OwnerAuthController::class, 'dashboard']);
        
        // ───────────────────────────────────────────────────────────────────
        // 🏢 Gestión de Empresas (Companies)
        // ───────────────────────────────────────────────────────────────────
        Route::prefix('companies')->group(function () {
            Route::get('/', [CompanyController::class, 'index']);
            Route::post('/', [CompanyController::class, 'store']);
            Route::get('/{id}', [CompanyController::class, 'show']);
            Route::put('/{id}', [CompanyController::class, 'update']);
            Route::patch('/{id}/toggle-status', [CompanyController::class, 'toggleStatus']);
            Route::delete('/{id}', [CompanyController::class, 'destroy']);
            Route::post('/{id}/restore', [CompanyController::class, 'restore']);
        });
    });
});

// ═══════════════════════════════════════════════════════════════════════════
// 🔵 EMPLOYEE - Panel de Trabajo con RBAC
// ═══════════════════════════════════════════════════════════════════════════

Route::prefix('employee')->group(function () {
    
    // Autenticación
    Route::post('login', [EmployeeAuthController::class, 'login']);
    Route::post('login/sso', [EmployeeAuthController::class, 'loginSSO']);
    
    // Rutas protegidas
    Route::middleware(['auth:employee'])->group(function () {
        Route::get('me', [EmployeeAuthController::class, 'me']);
        Route::post('logout', [EmployeeAuthController::class, 'logout']);
        Route::post('refresh', [EmployeeAuthController::class, 'refresh']);
        Route::post('check-permission', [EmployeeAuthController::class, 'checkPermission']);
        
        // ───────────────────────────────────────────────────────────────────
        // � Gestión de Empleados (Employees)
        // ───────────────────────────────────────────────────────────────────
        Route::prefix('employees')->group(function () {
            Route::get('/', [EmployeeController::class, 'index']);
            Route::post('/', [EmployeeController::class, 'store']);
            Route::get('/{id}', [EmployeeController::class, 'show']);
            Route::put('/{id}', [EmployeeController::class, 'update']);
            Route::patch('/{id}/toggle-status', [EmployeeController::class, 'toggleStatus']);
            Route::delete('/{id}', [EmployeeController::class, 'destroy']);
            
            // Gestión de Roles de empleados
            Route::post('/{id}/roles', [EmployeeController::class, 'assignRole']);
            Route::delete('/{id}/roles/{roleId}', [EmployeeController::class, 'removeRole']);
        });
        
        // ───────────────────────────────────────────────────────────────────
        // 🎭 Gestión de Roles (Roles & Permissions)
        // ───────────────────────────────────────────────────────────────────
        Route::prefix('roles')->group(function () {
            // Obtener todos los permisos disponibles (debe ir primero)
            Route::get('/permissions', [RoleController::class, 'getPermissions']);
            
            Route::get('/', [RoleController::class, 'index']);
            Route::post('/', [RoleController::class, 'store']);
            Route::get('/{id}', [RoleController::class, 'show']);
            Route::put('/{id}', [RoleController::class, 'update']);
            Route::delete('/{id}', [RoleController::class, 'destroy']);
        });
        
        // ───────────────────────────────────────────────────────────────────
        // �📁 Módulo: Archivos
        // ───────────────────────────────────────────────────────────────────
        Route::prefix('files')->group(function () {
            Route::get('/', function () {
                return response()->json(['message' => 'Lista de archivos - Requiere permiso: files.view']);
            })->middleware('permission:files.view');
            
            Route::post('/', function () {
                return response()->json(['message' => 'Subir archivo - Requiere permiso: files.upload']);
            })->middleware('permission:files.upload');
            
            Route::get('/{id}/download', function ($id) {
                return response()->json(['message' => "Descargar archivo {$id} - Requiere permiso: files.download"]);
            })->middleware('permission:files.download');
            
            Route::delete('/{id}', function ($id) {
                return response()->json(['message' => "Eliminar archivo {$id} - Requiere permiso: files.delete"]);
            })->middleware('permission:files.delete');
        });
        
        // ───────────────────────────────────────────────────────────────────
        // 📄 Módulo: Facturas
        // ───────────────────────────────────────────────────────────────────
        Route::prefix('invoices')->group(function () {
            Route::get('/', function () {
                return response()->json(['message' => 'Lista de facturas - Requiere permiso: invoices.view']);
            })->middleware('permission:invoices.view');
            
            Route::post('/', function () {
                return response()->json(['message' => 'Crear factura - Requiere permiso: invoices.create']);
            })->middleware('permission:invoices.create');
            
            Route::put('/{id}', function ($id) {
                return response()->json(['message' => "Editar factura {$id} - Requiere permiso: invoices.edit"]);
            })->middleware('permission:invoices.edit');
            
            Route::post('/{id}/authorize', function ($id) {
                return response()->json(['message' => "Autorizar factura {$id} en SRI - Requiere permiso: invoices.authorize"]);
            })->middleware('permission:invoices.authorize');
        });
        
        // ───────────────────────────────────────────────────────────────────
        //  Módulo: Reportes
        // ───────────────────────────────────────────────────────────────────
        Route::prefix('reports')->group(function () {
            Route::get('/', function () {
                return response()->json(['message' => 'Ver reportes - Requiere permiso: reports.view']);
            })->middleware('permission:reports.view');
            
            Route::get('/export', function () {
                return response()->json(['message' => 'Exportar reporte - Requiere permiso: reports.export']);
            })->middleware('permission:reports.export');
            
            Route::get('/analytics', function () {
                return response()->json(['message' => 'Dashboard analítico - Requiere permiso: reports.analytics']);
            })->middleware('permission:reports.analytics');
        });
    });
});

// ═══════════════════════════════════════════════════════════════════════════
// 📋 Rutas Públicas
// ═══════════════════════════════════════════════════════════════════════════

Route::get('health', function () {
    return response()->json([
        'success' => true,
        'message' => 'NetFacture API - Sistema funcionando correctamente',
        'version' => '2.0.0',
        'timestamp' => now()->toIso8601String(),
    ]);
});
