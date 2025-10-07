<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EstablishmentController extends Controller
{
    /**
     * Display a listing of establishments (Solo SuperAdmin).
     */
    public function index(Request $request): JsonResponse
    {
        // Solo superadmin puede listar todos los establecimientos
        if (!Auth::user()->hasRole('superadmin')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción',
            ], 403);
        }

        $query = Establishment::with(['users' => function ($q) {
            $q->select('id', 'name', 'email', 'establishment_id', 'is_establishment_admin', 'is_active');
        }]);

        // Filtrar por estado
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Búsqueda
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ruc', 'like', "%{$search}%")
                    ->orWhere('business_name', 'like', "%{$search}%")
                    ->orWhere('trade_name', 'like', "%{$search}%");
            });
        }

        $establishments = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $establishments->items(),
            'pagination' => [
                'total' => $establishments->total(),
                'per_page' => $establishments->perPage(),
                'current_page' => $establishments->currentPage(),
                'last_page' => $establishments->lastPage(),
            ],
        ]);
    }

    /**
     * Store a newly created establishment (Solo SuperAdmin).
     */
    public function store(Request $request): JsonResponse
    {
        // Solo superadmin puede crear establecimientos
        if (!Auth::user()->hasRole('superadmin')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción',
            ], 403);
        }

        $validated = $request->validate([
            'ruc' => 'required|string|size:13|unique:establishments,ruc',
            'business_name' => 'required|string|max:255',
            'trade_name' => 'nullable|string|max:255',
            'address' => 'required|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|email|max:255',
            'establishment_code' => 'required|string|size:3',
            'emission_point' => 'required|string|size:3',
            'environment' => ['required', Rule::in(['pruebas', 'produccion'])],
            'is_active' => 'boolean',
            'settings' => 'nullable|array',
            
            // Datos del administrador
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8',
        ]);

        try {
            DB::beginTransaction();

            // Crear establecimiento
            $establishment = Establishment::create([
                'ruc' => $validated['ruc'],
                'business_name' => $validated['business_name'],
                'trade_name' => $validated['trade_name'] ?? $validated['business_name'],
                'address' => $validated['address'],
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'],
                'establishment_code' => $validated['establishment_code'],
                'emission_point' => $validated['emission_point'],
                'environment' => $validated['environment'],
                'is_active' => $validated['is_active'] ?? true,
                'settings' => $validated['settings'] ?? [],
            ]);

            // Crear usuario administrador del establecimiento
            $admin = User::create([
                'name' => $validated['admin_name'],
                'email' => $validated['admin_email'],
                'password' => Hash::make($validated['admin_password']),
                'establishment_id' => $establishment->id,
                'is_establishment_admin' => true,
                'is_active' => true,
            ]);

            // Asignar rol de admin
            $admin->assignRole('admin');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Establecimiento creado exitosamente',
                'data' => [
                    'establishment' => $establishment->load('users'),
                    'admin' => [
                        'id' => $admin->id,
                        'name' => $admin->name,
                        'email' => $admin->email,
                    ],
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear establecimiento',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified establishment.
     */
    public function show(string $id): JsonResponse
    {
        $user = Auth::user();

        // Superadmin puede ver cualquier establecimiento
        if ($user->hasRole('superadmin')) {
            $establishment = Establishment::with(['users' => function ($q) {
                $q->select('id', 'name', 'email', 'establishment_id', 'is_establishment_admin', 'is_active')
                  ->with('roles:id,name');
            }])->find($id);
        } else {
            // Otros usuarios solo pueden ver su propio establecimiento
            $establishment = Establishment::where('id', $id)
                ->where('id', $user->establishment_id)
                ->with(['users' => function ($q) {
                    $q->select('id', 'name', 'email', 'establishment_id', 'is_establishment_admin', 'is_active')
                      ->with('roles:id,name');
                }])
                ->first();
        }

        if (!$establishment) {
            return response()->json([
                'success' => false,
                'message' => 'Establecimiento no encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $establishment,
        ]);
    }

    /**
     * Update the specified establishment.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $user = Auth::user();

        // Superadmin puede actualizar cualquier establecimiento
        if ($user->hasRole('superadmin')) {
            $establishment = Establishment::find($id);
        } else if ($user->hasRole('admin') && $user->is_establishment_admin) {
            // Admin de establecimiento solo puede actualizar su propio establecimiento
            $establishment = Establishment::where('id', $id)
                ->where('id', $user->establishment_id)
                ->first();
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción',
            ], 403);
        }

        if (!$establishment) {
            return response()->json([
                'success' => false,
                'message' => 'Establecimiento no encontrado',
            ], 404);
        }

        $validated = $request->validate([
            'ruc' => ['sometimes', 'string', 'size:13', Rule::unique('establishments')->ignore($id)],
            'business_name' => 'sometimes|string|max:255',
            'trade_name' => 'nullable|string|max:255',
            'address' => 'sometimes|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'sometimes|email|max:255',
            'establishment_code' => 'sometimes|string|size:3',
            'emission_point' => 'sometimes|string|size:3',
            'environment' => ['sometimes', Rule::in(['pruebas', 'produccion'])],
            'is_active' => 'sometimes|boolean',
            'settings' => 'nullable|array',
        ]);

        try {
            $establishment->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Establecimiento actualizado exitosamente',
                'data' => $establishment->fresh(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar establecimiento',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle establishment status (activate/deactivate).
     */
    public function toggleStatus(string $id): JsonResponse
    {
        // Solo superadmin puede cambiar el estado
        if (!Auth::user()->hasRole('superadmin')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción',
            ], 403);
        }

        $establishment = Establishment::find($id);

        if (!$establishment) {
            return response()->json([
                'success' => false,
                'message' => 'Establecimiento no encontrado',
            ], 404);
        }

        try {
            $establishment->update([
                'is_active' => !$establishment->is_active,
            ]);

            $status = $establishment->is_active ? 'activado' : 'desactivado';

            return response()->json([
                'success' => true,
                'message' => "Establecimiento {$status} exitosamente",
                'data' => $establishment,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado del establecimiento',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified establishment (soft delete).
     */
    public function destroy(string $id): JsonResponse
    {
        // Solo superadmin puede eliminar establecimientos
        if (!Auth::user()->hasRole('superadmin')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción',
            ], 403);
        }

        $establishment = Establishment::find($id);

        if (!$establishment) {
            return response()->json([
                'success' => false,
                'message' => 'Establecimiento no encontrado',
            ], 404);
        }

        try {
            // Soft delete
            $establishment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Establecimiento eliminado exitosamente',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar establecimiento',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get authenticated user's establishment.
     */
    public function myEstablishment(): JsonResponse
    {
        $user = Auth::user();

        if (!$user->establishment_id) {
            return response()->json([
                'success' => false,
                'message' => 'No perteneces a ningún establecimiento',
            ], 404);
        }

        $establishment = Establishment::with(['users' => function ($q) {
            $q->select('id', 'name', 'email', 'establishment_id', 'is_establishment_admin', 'is_active')
              ->with('roles:id,name');
        }])->find($user->establishment_id);

        return response()->json([
            'success' => true,
            'data' => $establishment,
        ]);
    }
}
