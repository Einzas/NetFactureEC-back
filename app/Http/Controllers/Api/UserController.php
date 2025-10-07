<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $search = $request->input('search');
            $user = auth()->user();

            $query = User::with('roles', 'permissions', 'establishment:id,ruc,business_name');

            // Filtrar por establishment si no es superadmin
            if (!$user->hasRole('superadmin')) {
                $query->where('establishment_id', $user->establishment_id);
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $users = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => UserResource::collection($users),
                'meta' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuarios',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $authUser = auth()->user();

            // Determinar el establishment_id
            $establishmentId = null;
            if ($authUser->hasRole('superadmin')) {
                // SuperAdmin puede especificar el establishment o dejarlo null
                $establishmentId = $request->input('establishment_id');
            } else {
                // Admin solo puede crear usuarios en su propio establishment
                $establishmentId = $authUser->establishment_id;
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'is_active' => $request->input('is_active', true),
                'establishment_id' => $establishmentId,
                'is_establishment_admin' => $request->input('is_establishment_admin', false),
            ]);

            if ($request->has('roles')) {
                $user->syncRoles($request->roles);
            }

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'data' => new UserResource($user->load('roles', 'permissions', 'establishment'))
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $authUser = auth()->user();
            $query = User::with('roles', 'permissions', 'establishment:id,ruc,business_name');

            // Filtrar por establishment si no es superadmin
            if (!$authUser->hasRole('superadmin')) {
                $query->where('establishment_id', $authUser->establishment_id);
            }

            $user = $query->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new UserResource($user)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        try {
            $authUser = auth()->user();
            $query = User::query();

            // Filtrar por establishment si no es superadmin
            if (!$authUser->hasRole('superadmin')) {
                $query->where('establishment_id', $authUser->establishment_id);
            }

            $user = $query->findOrFail($id);

            $data = $request->only(['name', 'email', 'phone', 'is_active']);

            if ($request->has('password')) {
                $data['password'] = Hash::make($request->password);
            }

            // Solo superadmin puede cambiar el establishment_id
            if ($authUser->hasRole('superadmin') && $request->has('establishment_id')) {
                $data['establishment_id'] = $request->establishment_id;
            }

            // Solo establishment admin puede cambiar is_establishment_admin
            if ($authUser->isEstablishmentAdmin() && $request->has('is_establishment_admin')) {
                $data['is_establishment_admin'] = $request->is_establishment_admin;
            }

            $user->update($data);

            if ($request->has('roles')) {
                $user->syncRoles($request->roles);
            }

            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado exitosamente',
                'data' => new UserResource($user->load('roles', 'permissions', 'establishment'))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $authUser = auth()->user();
            $query = User::query();

            // Filtrar por establishment si no es superadmin
            if (!$authUser->hasRole('superadmin')) {
                $query->where('establishment_id', $authUser->establishment_id);
            }

            $user = $query->findOrFail($id);
            
            // Prevent deleting own account
            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes eliminar tu propia cuenta'
                ], 403);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

