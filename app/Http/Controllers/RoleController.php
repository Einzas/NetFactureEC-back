<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $employee = auth('employee')->user();
            
            // Solo empleados con permiso 'roles.view' pueden ver roles
            if (!$employee->can('roles.view')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para ver roles'
                ], 403);
            }

            $query = Role::with('permissions')
                          ->withCount(['permissions', 'employees']);

            // Filtros
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'name');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Opción para paginar o devolver todos
            if ($request->get('paginate', true)) {
                $perPage = $request->get('per_page', 15);
                $roles = $query->paginate($perPage);

                return response()->json([
                    'success' => true,
                    'message' => 'Roles obtenidos exitosamente',
                    'data' => [
                        'roles' => $roles->items(),
                        'pagination' => [
                            'total' => $roles->total(),
                            'per_page' => $roles->perPage(),
                            'current_page' => $roles->currentPage(),
                            'last_page' => $roles->lastPage(),
                        ]
                    ]
                ], 200);
            } else {
                $roles = $query->get();

                return response()->json([
                    'success' => true,
                    'message' => 'Roles obtenidos exitosamente',
                    'data' => [
                        'roles' => $roles
                    ]
                ], 200);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener roles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified role.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $employee = auth('employee')->user();
            
            if (!$employee->can('roles.view')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para ver roles'
                ], 403);
            }

            $role = Role::with('permissions')
                        ->withCount(['permissions', 'employees'])
                        ->find($id);

            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rol no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Rol obtenido exitosamente',
                'data' => [
                    'role' => $role
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener rol',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created role.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $employee = auth('employee')->user();
            
            if (!$employee->can('roles.create')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para crear roles'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:50|unique:roles,name',
                'description' => 'nullable|string|max:255',
                'permissions' => 'required|array|min:1',
                'permissions.*' => 'exists:permissions,id',
            ], [
                'name.unique' => 'Ya existe un rol con este nombre',
                'permissions.required' => 'Debes asignar al menos un permiso',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Crear rol
            $role = Role::create([
                'name' => $request->name,
                'display_name' => $request->display_name ?? ucwords(str_replace('_', ' ', $request->name)),
                'description' => $request->description,
            ]);

            // Asignar permisos
            $permissions = Permission::whereIn('id', $request->permissions)->get();
            $role->syncPermissions($permissions);

            $role->load('permissions');

            return response()->json([
                'success' => true,
                'message' => 'Rol creado exitosamente',
                'data' => [
                    'role' => $role
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear rol',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified role.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $employee = auth('employee')->user();
            
            if (!$employee->can('roles.edit')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para editar roles'
                ], 403);
            }

            $role = Role::find($id);

            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rol no encontrado'
                ], 404);
            }

            // Prevenir edición de roles del sistema
            $systemRoles = ['admin', 'contador', 'facturador', 'vendedor', 'auditor', 'asistente'];
            if (in_array($role->name, $systemRoles)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden editar roles del sistema'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:50|unique:roles,name,' . $id,
                'description' => 'nullable|string|max:255',
                'permissions' => 'sometimes|required|array|min:1',
                'permissions.*' => 'exists:permissions,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Actualizar datos básicos
            $updateData = $request->only(['name', 'description', 'display_name']);
            $role->update($updateData);

            // Actualizar permisos
            if ($request->has('permissions')) {
                $permissions = Permission::whereIn('id', $request->permissions)->get();
                $role->syncPermissions($permissions);
            }

            $role->load('permissions');

            return response()->json([
                'success' => true,
                'message' => 'Rol actualizado exitosamente',
                'data' => [
                    'role' => $role
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar rol',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified role.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $employee = auth('employee')->user();
            
            if (!$employee->can('roles.delete')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para eliminar roles'
                ], 403);
            }

            $role = Role::find($id);

            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rol no encontrado'
                ], 404);
            }

            // Prevenir eliminación de roles del sistema
            $systemRoles = ['admin', 'contador', 'facturador', 'vendedor', 'auditor', 'asistente'];
            if (in_array($role->name, $systemRoles)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden eliminar roles del sistema'
                ], 403);
            }

            // Verificar si hay empleados con este rol
            $employeesCount = $role->employees()->count();
            if ($employeesCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar un rol que está asignado a empleados',
                    'data' => [
                        'employees_count' => $employeesCount
                    ]
                ], 422);
            }

            $role->delete();

            return response()->json([
                'success' => true,
                'message' => 'Rol eliminado exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar rol',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all available permissions.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPermissions(Request $request)
    {
        try {
            $employee = auth('employee')->user();
            
            if (!$employee->can('roles.view')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para ver permisos'
                ], 403);
            }

            $permissions = Permission::all();

            // Agrupar permisos por módulo
            $groupedPermissions = [];
            foreach ($permissions->groupBy(function($permission) {
                return explode('.', $permission->name)[0];
            }) as $moduleName => $modulePermissions) {
                $groupedPermissions[] = [
                    'module' => $moduleName,
                    'permissions' => $modulePermissions->map(function($p) {
                        return [
                            'id' => $p->id,
                            'name' => $p->name,
                            'display_name' => $p->display_name,
                            'description' => $p->description ?? '',
                        ];
                    })->values()->all()
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Permisos obtenidos exitosamente',
                'data' => [
                    'permissions' => $groupedPermissions,
                    'total' => $permissions->count(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener permisos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
