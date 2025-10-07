<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    /**
     * Listar todas las empresas del owner
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $owner = auth('owner')->user();
            // Owners tienen acceso automático a todas sus empresas
            
            $query = Company::where('owner_id', $owner->id);

            // Filtros opcionales
            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('business_name', 'like', "%{$search}%")
                      ->orWhere('trade_name', 'like', "%{$search}%")
                      ->orWhere('ruc', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $companies = $query->withCount('employees')
                              ->orderBy('created_at', 'desc')
                              ->paginate($request->input('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $companies
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las empresas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nueva empresa
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $owner = auth('owner')->user();
            // Owners tienen acceso automático a todas sus empresas
            
            $validator = Validator::make($request->all(), [
                'ruc' => [
                    'required',
                    'string',
                    'size:13',
                    'regex:/^\d{13}$/',
                    Rule::unique('companies')->whereNull('deleted_at')
                ],
                'business_name' => 'required|string|max:255',
                'trade_name' => 'nullable|string|max:255',
                'address' => 'required|string|max:500',
                'city' => 'nullable|string|max:100',
                'province' => 'nullable|string|max:100',
                'phone' => 'nullable|string|max:20',
                'email' => [
                    'required',
                    'email',
                    'max:255',
                    Rule::unique('companies')->whereNull('deleted_at')
                ],
                'is_active' => 'boolean'
            ], [
                'ruc.required' => 'El RUC es obligatorio',
                'ruc.size' => 'El RUC debe tener exactamente 13 dígitos',
                'ruc.regex' => 'El RUC debe contener solo números',
                'ruc.unique' => 'Este RUC ya está registrado',
                'business_name.required' => 'La razón social es obligatoria',
                'address.required' => 'La dirección es obligatoria',
                'email.required' => 'El email es obligatorio',
                'email.email' => 'El email debe ser válido',
                'email.unique' => 'Este email ya está registrado',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $company = Company::create([
                'owner_id' => $owner->id,
                'ruc' => $request->ruc,
                'business_name' => $request->business_name,
                'trade_name' => $request->trade_name,
                'address' => $request->address,
                'city' => $request->city ?? 'Quito',
                'province' => $request->province ?? 'Pichincha',
                'phone' => $request->phone,
                'email' => $request->email,
                'is_active' => $request->boolean('is_active', true)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Empresa creada exitosamente',
                'data' => $company->load('owner')
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la empresa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar una empresa específica
     */
    public function show(string $id): JsonResponse
    {
        try {
            $owner = auth('owner')->user();
            // Owners tienen acceso automático a todas sus empresas
            
            $company = Company::where('owner_id', $owner->id)
                              ->withCount('employees')
                              ->find($id);

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Empresa no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $company->load('owner')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la empresa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar empresa
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $owner = auth('owner')->user();
            // Owners tienen acceso automático a todas sus empresas
            
            $company = Company::where('owner_id', $owner->id)->find($id);

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Empresa no encontrada'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'ruc' => [
                    'sometimes',
                    'required',
                    'string',
                    'size:13',
                    'regex:/^\d{13}$/',
                    Rule::unique('companies')->ignore($company->id)->whereNull('deleted_at')
                ],
                'business_name' => 'sometimes|required|string|max:255',
                'trade_name' => 'nullable|string|max:255',
                'address' => 'sometimes|required|string|max:500',
                'city' => 'nullable|string|max:100',
                'province' => 'nullable|string|max:100',
                'phone' => 'nullable|string|max:20',
                'email' => [
                    'sometimes',
                    'required',
                    'email',
                    'max:255',
                    Rule::unique('companies')->ignore($company->id)->whereNull('deleted_at')
                ],
                'is_active' => 'boolean'
            ], [
                'ruc.size' => 'El RUC debe tener exactamente 13 dígitos',
                'ruc.regex' => 'El RUC debe contener solo números',
                'ruc.unique' => 'Este RUC ya está registrado',
                'business_name.required' => 'La razón social es obligatoria',
                'address.required' => 'La dirección es obligatoria',
                'email.email' => 'El email debe ser válido',
                'email.unique' => 'Este email ya está registrado',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $company->update($request->only([
                'ruc',
                'business_name',
                'trade_name',
                'address',
                'city',
                'province',
                'phone',
                'email',
                'is_active'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Empresa actualizada exitosamente',
                'data' => $company->fresh()->load('owner')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la empresa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activar/Desactivar empresa
     */
    public function toggleStatus(string $id): JsonResponse
    {
        try {
            $owner = auth('owner')->user();
            // Owners tienen acceso automático a todas sus empresas
            
            $company = Company::where('owner_id', $owner->id)->find($id);

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Empresa no encontrada'
                ], 404);
            }

            $company->is_active = !$company->is_active;
            $company->save();

            return response()->json([
                'success' => true,
                'message' => $company->is_active ? 'Empresa activada' : 'Empresa desactivada',
                'data' => $company
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado de la empresa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar empresa (soft delete)
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $owner = auth('owner')->user();
            // Owners tienen acceso automático a todas sus empresas
            
            $company = Company::where('owner_id', $owner->id)->find($id);

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Empresa no encontrada'
                ], 404);
            }

            // Verificar si tiene empleados activos
            if ($company->employees()->where('is_active', true)->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar una empresa con empleados activos'
                ], 422);
            }

            $company->delete();

            return response()->json([
                'success' => true,
                'message' => 'Empresa eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la empresa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restaurar empresa eliminada
     */
    public function restore(string $id): JsonResponse
    {
        try {
            $owner = auth('owner')->user();
            // Owners tienen acceso automático a todas sus empresas
            
            $company = Company::where('owner_id', $owner->id)
                              ->onlyTrashed()
                              ->find($id);

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Empresa no encontrada'
                ], 404);
            }

            $company->restore();

            return response()->json([
                'success' => true,
                'message' => 'Empresa restaurada exitosamente',
                'data' => $company
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al restaurar la empresa',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
