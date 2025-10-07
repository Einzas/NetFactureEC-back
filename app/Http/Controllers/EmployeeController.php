<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Company;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $employee = auth('employee')->user();
            
            // Solo los empleados con permiso 'employees.view' pueden ver la lista
            if (!$employee->can('employees.view')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para ver empleados'
                ], 403);
            }

            $query = Employee::where('company_id', $employee->company_id)
                            ->with(['roles.permissions']);

            // Filtros
            if ($request->has('status')) {
                $query->where('is_active', $request->status === 'active');
            }

            if ($request->has('role')) {
                $query->whereHas('roles', function($q) use ($request) {
                    $q->where('name', $request->role);
                });
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('identification', 'like', "%{$search}%");
                });
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginación
            $perPage = $request->get('per_page', 15);
            $employees = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Empleados obtenidos exitosamente',
                'data' => [
                    'employees' => $employees->items(),
                    'pagination' => [
                        'total' => $employees->total(),
                        'per_page' => $employees->perPage(),
                        'current_page' => $employees->currentPage(),
                        'last_page' => $employees->lastPage(),
                        'from' => $employees->firstItem(),
                        'to' => $employees->lastItem(),
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener empleados',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created employee.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $employee = auth('employee')->user();
            
            // Solo los empleados con permiso 'employees.create' pueden crear
            if (!$employee->can('employees.create')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para crear empleados'
                ], 403);
            }

            $company = $employee->company;

            // Validación
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:employees,email',
                'identification' => 'required|string|size:13|regex:/^[0-9]{13}$/|unique:employees,identification',
                'password' => 'required|string|min:8|confirmed',
                'phone' => 'nullable|string|max:20',
                'roles' => 'required|array|min:1',
                'roles.*' => 'exists:roles,name',
            ], [
                'email.unique' => 'El email ya está registrado',
                'identification.unique' => 'La cédula ya está registrada',
                'identification.size' => 'La cédula debe tener exactamente 13 dígitos',
                'identification.regex' => 'La cédula debe contener solo números',
                'roles.required' => 'Debe asignar al menos un rol',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Crear empleado
            $employeeData = $request->only([
                'name',
                'email',
                'identification',
                'phone',
            ]);

            $employeeData['company_id'] = $company->id;
            $employeeData['password'] = Hash::make($request->password);

            $newEmployee = Employee::create($employeeData);

            // Asignar roles
            $roles = Role::whereIn('name', $request->roles)->get();
            $newEmployee->assignRole($roles);

            // Cargar relaciones
            $newEmployee->load('roles.permissions');

            return response()->json([
                'success' => true,
                'message' => 'Empleado creado exitosamente',
                'data' => [
                    'employee' => $newEmployee
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear empleado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified employee.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $employee = auth('employee')->user();
            
            // Verificar permiso
            if (!$employee->can('employees.view') && $employee->id != $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para ver este empleado'
                ], 403);
            }

            $targetEmployee = Employee::where('company_id', $employee->company_id)
                                      ->where('id', $id)
                                      ->with(['roles.permissions', 'company'])
                                      ->first();

            if (!$targetEmployee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Empleado no encontrado'
                ], 404);
            }

            // Agregar permisos y roles en el objeto employee
            $employeeData = $targetEmployee->toArray();
            $employeeData['permissions'] = $targetEmployee->getAllPermissions()->pluck('name');
            $employeeData['roles'] = $targetEmployee->roles;

            return response()->json([
                'success' => true,
                'message' => 'Empleado obtenido exitosamente',
                'data' => [
                    'employee' => $employeeData
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener empleado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified employee.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $employee = auth('employee')->user();
            
            // Verificar permiso
            $isSelfUpdate = $employee->id == $id;
            if (!$isSelfUpdate && !$employee->can('employees.edit')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para editar empleados'
                ], 403);
            }

            $targetEmployee = Employee::where('company_id', $employee->company_id)
                                      ->where('id', $id)
                                      ->first();

            if (!$targetEmployee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Empleado no encontrado'
                ], 404);
            }

            // Validación
            $rules = [
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|max:255|unique:employees,email,' . $id,
                'identification' => 'sometimes|required|string|size:13|regex:/^[0-9]{13}$/|unique:employees,identification,' . $id,
                'phone' => 'nullable|string|max:20',
                'password' => 'nullable|string|min:8|confirmed',
            ];

            // Solo admins pueden cambiar roles
            if ($employee->can('employees.edit') && !$isSelfUpdate) {
                $rules['roles'] = 'sometimes|array|min:1';
                $rules['roles.*'] = 'exists:roles,name';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Actualizar datos básicos
            $updateData = $request->only([
                'name',
                'email',
                'identification',
                'phone',
            ]);

            // Actualizar contraseña si se proporciona
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $targetEmployee->update($updateData);

            // Actualizar roles (solo si tiene permiso y no es auto-actualización)
            if ($request->has('roles') && $employee->can('employees.edit') && !$isSelfUpdate) {
                $roles = Role::whereIn('name', $request->roles)->get();
                $targetEmployee->syncRoles($roles);
            }

            // Recargar relaciones
            $targetEmployee->load('roles.permissions');

            return response()->json([
                'success' => true,
                'message' => 'Empleado actualizado exitosamente',
                'data' => [
                    'employee' => $targetEmployee
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar empleado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle employee status (activate/deactivate).
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleStatus($id)
    {
        try {
            $employee = auth('employee')->user();
            
            // Solo admins pueden cambiar estados
            if (!$employee->can('employees.edit')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para cambiar el estado de empleados'
                ], 403);
            }

            $targetEmployee = Employee::where('company_id', $employee->company_id)
                                      ->where('id', $id)
                                      ->first();

            if (!$targetEmployee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Empleado no encontrado'
                ], 404);
            }

            // No se puede desactivar a sí mismo
            if ($targetEmployee->id === $employee->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes desactivar tu propia cuenta'
                ], 403);
            }

            $targetEmployee->is_active = !$targetEmployee->is_active;
            $targetEmployee->save();

            $status = $targetEmployee->is_active ? 'activado' : 'desactivado';

            return response()->json([
                'success' => true,
                'message' => "Empleado {$status} exitosamente",
                'data' => [
                    'employee' => $targetEmployee,
                    'is_active' => $targetEmployee->is_active
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado del empleado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified employee (soft delete).
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $employee = auth('employee')->user();
            
            // Solo admins pueden eliminar
            if (!$employee->can('employees.delete')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para eliminar empleados'
                ], 403);
            }

            $targetEmployee = Employee::where('company_id', $employee->company_id)
                                      ->where('id', $id)
                                      ->first();

            if (!$targetEmployee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Empleado no encontrado'
                ], 404);
            }

            // No se puede eliminar a sí mismo
            if ($targetEmployee->id === $employee->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes eliminar tu propia cuenta'
                ], 400);
            }

            // Soft delete
            $targetEmployee->delete();

            return response()->json([
                'success' => true,
                'message' => 'Empleado eliminado exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar empleado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign a role to an employee.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignRole(Request $request, $id)
    {
        try {
            $employee = auth('employee')->user();
            
            // Solo admins pueden asignar roles
            if (!$employee->can('employees.edit')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para asignar roles'
                ], 403);
            }

            $targetEmployee = Employee::where('company_id', $employee->company_id)
                                      ->where('id', $id)
                                      ->first();

            if (!$targetEmployee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Empleado no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'role_id' => 'required|exists:roles,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $role = Role::find($request->role_id);
            $targetEmployee->assignRole($role);

            $targetEmployee->load('roles.permissions');

            return response()->json([
                'success' => true,
                'message' => 'Rol asignado exitosamente',
                'data' => [
                    'employee' => $targetEmployee
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar rol',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove a role from an employee.
     *
     * @param int $id
     * @param int $roleId
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeRole($id, $roleId)
    {
        try {
            $employee = auth('employee')->user();
            
            // Solo admins pueden remover roles
            if (!$employee->can('employees.edit')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para remover roles'
                ], 403);
            }

            $targetEmployee = Employee::where('company_id', $employee->company_id)
                                      ->where('id', $id)
                                      ->first();

            if (!$targetEmployee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Empleado no encontrado'
                ], 404);
            }

            $role = Role::find($roleId);
            
            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rol no encontrado'
                ], 404);
            }
            
            // Verificar que el empleado tenga al menos 2 roles antes de remover
            if ($targetEmployee->roles()->count() <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'El empleado debe tener al menos un rol'
                ], 400);
            }

            $targetEmployee->removeRole($role);

            $targetEmployee->load('roles.permissions');

            return response()->json([
                'success' => true,
                'message' => 'Rol removido exitosamente',
                'data' => [
                    'employee' => $targetEmployee
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al remover rol',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
