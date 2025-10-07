<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EstablishmentController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes - Authentication
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes
Route::middleware(['jwt.auth'])->group(function () {
    // Auth endpoints
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });

    // File management (all authenticated users)
    Route::prefix('files')->group(function () {
        Route::get('/', [FileController::class, 'index']);
        Route::post('/', [FileController::class, 'store']);
        Route::get('/{id}', [FileController::class, 'show']);
        Route::get('/{id}/download', [FileController::class, 'download']);
        Route::delete('/{id}', [FileController::class, 'destroy']);
        Route::delete('/cleanup/expired', [FileController::class, 'cleanExpired']);
        Route::delete('/cleanup/session', [FileController::class, 'cleanSessionFiles']);
    });

    // Establishment routes
    Route::prefix('establishments')->group(function () {
        // Ver mi establecimiento (todos los usuarios autenticados)
        Route::get('/my-establishment', [EstablishmentController::class, 'myEstablishment']);
        
        // CRUD de establecimientos (solo superadmin y admin)
        Route::get('/', [EstablishmentController::class, 'index']);
        Route::post('/', [EstablishmentController::class, 'store']);
        Route::get('/{id}', [EstablishmentController::class, 'show']);
        Route::put('/{id}', [EstablishmentController::class, 'update']);
        Route::patch('/{id}/toggle-status', [EstablishmentController::class, 'toggleStatus']);
        Route::delete('/{id}', [EstablishmentController::class, 'destroy']);
    });

    // User management (requires admin role)
    Route::middleware(['role:admin,superadmin'])->group(function () {
        Route::apiResource('users', UserController::class);
        Route::apiResource('roles', RoleController::class);
        Route::apiResource('permissions', PermissionController::class);
    });
});

