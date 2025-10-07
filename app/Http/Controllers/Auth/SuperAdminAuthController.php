<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SuperAdminAuthController extends Controller
{
    /**
     * Login de superadministrador
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)
            ->where('type', 'superadmin')
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Tu cuenta está inactiva. Contacta al administrador.',
            ], 403);
        }

        // Generar token JWT con guard superadmin
        $token = auth('superadmin')->login($user);

        // Actualizar último login
        $user->updateLastLogin($request->ip());

        return response()->json([
            'success' => true,
            'message' => 'Login exitoso',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'type' => $user->type,
                    'avatar' => $user->avatar,
                ],
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('superadmin')->factory()->getTTL() * 60,
            ],
        ]);
    }

    /**
     * Obtener información del superadmin autenticado
     */
    public function me(): JsonResponse
    {
        $user = auth('superadmin')->user();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'type' => $user->type,
                'phone' => $user->phone,
                'avatar' => $user->avatar,
                'is_active' => $user->is_active,
                'last_login_at' => $user->last_login_at,
            ],
        ]);
    }

    /**
     * Refrescar token
     */
    public function refresh(): JsonResponse
    {
        $token = auth('superadmin')->refresh();

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('superadmin')->factory()->getTTL() * 60,
            ],
        ]);
    }

    /**
     * Logout
     */
    public function logout(): JsonResponse
    {
        auth('superadmin')->logout();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada exitosamente',
        ]);
    }

    /**
     * Dashboard analytics para superadmin
     */
    public function dashboard(): JsonResponse
    {
        $stats = [
            'total_owners' => User::where('type', 'owner')->count(),
            'active_owners' => User::where('type', 'owner')->where('is_active', true)->count(),
            'total_companies' => \App\Models\Company::count(),
            'active_companies' => \App\Models\Company::where('is_active', true)->count(),
            'total_employees' => \App\Models\Employee::count(),
            'active_employees' => \App\Models\Employee::where('is_active', true)->count(),
            'total_files' => \App\Models\UploadedFile::count(),
            'storage_used_mb' => round(\App\Models\UploadedFile::sum('size') / 1024 / 1024, 2),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
