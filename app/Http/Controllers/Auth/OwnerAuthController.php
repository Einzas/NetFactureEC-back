<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class OwnerAuthController extends Controller
{
    /**
     * Login de owner (dueño de empresa)
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)
            ->where('type', 'owner')
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

        // Generar token JWT con guard owner
        $token = auth('owner')->login($user);

        // Actualizar último login
        $user->updateLastLogin($request->ip());

        // Cargar empresas del owner
        $companies = $user->companies()
            ->select('id', 'ruc', 'business_name', 'trade_name', 'is_active')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Login exitoso',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'type' => $user->type,
                    'phone' => $user->phone,
                    'avatar' => $user->avatar,
                ],
                'companies' => $companies,
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('owner')->factory()->getTTL() * 60,
            ],
        ]);
    }

    /**
     * Obtener información del owner autenticado
     */
    public function me(): JsonResponse
    {
        $user = auth('owner')->user();
        
        $companies = $user->companies()
            ->with(['employees' => function ($query) {
                $query->where('is_active', true)->select('id', 'company_id', 'name', 'email', 'position');
            }])
            ->get();

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
                'companies' => $companies,
            ],
        ]);
    }

    /**
     * Refrescar token
     */
    public function refresh(): JsonResponse
    {
        $token = auth('owner')->refresh();

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('owner')->factory()->getTTL() * 60,
            ],
        ]);
    }

    /**
     * Logout
     */
    public function logout(): JsonResponse
    {
        auth('owner')->logout();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada exitosamente',
        ]);
    }

    /**
     * Dashboard del owner
     */
    public function dashboard(): JsonResponse
    {
        $user = auth('owner')->user();
        
        $stats = [
            'total_companies' => $user->companies()->count(),
            'active_companies' => $user->companies()->where('is_active', true)->count(),
            'total_employees' => \App\Models\Employee::whereIn('company_id', $user->companies->pluck('id'))->count(),
            'active_employees' => \App\Models\Employee::whereIn('company_id', $user->companies->pluck('id'))->where('is_active', true)->count(),
            'total_files' => \App\Models\UploadedFile::whereIn('company_id', $user->companies->pluck('id'))->count(),
            'storage_used_mb' => round(\App\Models\UploadedFile::whereIn('company_id', $user->companies->pluck('id'))->sum('size') / 1024 / 1024, 2),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
