<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class EmployeeAuthController extends Controller
{
    /**
     * Login de empleado
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $employee = Employee::where('email', $request->email)
            ->with(['company:id,ruc,business_name,trade_name,is_active'])
            ->first();

        if (!$employee || !Hash::check($request->password, $employee->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }

        // Verificar que el empleado esté activo
        if (!$employee->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Tu cuenta está inactiva. Contacta al administrador.',
            ], 403);
        }

        // Verificar que la empresa esté activa
        if (!$employee->company->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'La empresa se encuentra inactiva. Contacte al administrador.',
            ], 403);
        }

        // Generar token JWT con guard employee
        $token = auth('employee')->login($employee);

        // Actualizar último login
        $employee->updateLastLogin($request->ip());

        // Obtener roles y permisos
        $roles = $employee->roles()->select('roles.id', 'roles.name', 'roles.display_name')->get();
        $permissions = $employee->getAllPermissions()->pluck('name');

        return response()->json([
            'success' => true,
            'message' => 'Login exitoso',
            'data' => [
                'employee' => [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'email' => $employee->email,
                    'phone' => $employee->phone,
                    'company' => $employee->company,
                ],
                'roles' => $roles,
                'permissions' => $permissions,
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('employee')->factory()->getTTL() * 60,
            ],
        ]);
    }

    /**
     * Login con SSO (Google, Microsoft, etc.) - DESHABILITADO
     * Los campos sso_provider y sso_id fueron removidos del esquema
     */
    // public function loginSSO(Request $request): JsonResponse
    // {
    //     // Implementación futura si se necesita SSO
    // }

    /**
     * Obtener información del empleado autenticado
     */
    public function me(): JsonResponse
    {
        $employee = auth('employee')->user();
        
        $employee->load(['company', 'roles']);
        $permissions = $employee->getAllPermissions()->pluck('name');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'email' => $employee->email,
                'identification' => $employee->identification,
                'phone' => $employee->phone,
                'is_active' => $employee->is_active,
                'last_login_at' => $employee->last_login_at,
                'company' => $employee->company,
                'roles' => $employee->roles,
                'permissions' => $permissions,
            ],
        ]);
    }

    /**
     * Refrescar token
     */
    public function refresh(): JsonResponse
    {
        $token = auth('employee')->refresh();

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('employee')->factory()->getTTL() * 60,
            ],
        ]);
    }

    /**
     * Logout
     */
    public function logout(): JsonResponse
    {
        auth('employee')->logout();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada exitosamente',
        ]);
    }

    /**
     * Verificar permiso específico
     */
    public function checkPermission(Request $request): JsonResponse
    {
        $request->validate([
            'permission' => 'required|string',
        ]);

        $employee = auth('employee')->user();
        $hasPermission = $employee->can($request->permission);

        return response()->json([
            'success' => true,
            'data' => [
                'permission' => $request->permission,
                'has_permission' => $hasPermission,
            ],
        ]);
    }
}
