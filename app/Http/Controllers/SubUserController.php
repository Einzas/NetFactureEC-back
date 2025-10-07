<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\SubUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SubUserController extends Controller
{
    /**
     * Listar sub-usuarios de una empresa.
     */
    public function index($companyId)
    {
        $company = auth()->user()->companies()->findOrFail($companyId);
        
        $subUsers = $company->subUsers()
            ->when(request('search'), function($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('document_number', 'like', "%{$search}%");
                });
            })
            ->when(request('is_active') !== null, function($query) {
                $query->where('is_active', request('is_active'));
            })
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $subUsers->items(),
            'pagination' => [
                'total' => $subUsers->total(),
                'per_page' => $subUsers->perPage(),
                'current_page' => $subUsers->currentPage(),
                'last_page' => $subUsers->lastPage(),
            ],
        ]);
    }

    /**
     * Crear nuevo sub-usuario.
     */
    public function store(Request $request, $companyId)
    {
        $company = auth()->user()->companies()->findOrFail($companyId);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:sub_users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'document_number' => 'nullable|string|max:20',
        ]);

        $subUser = $company->subUsers()->create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'document_number' => $request->document_number,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cliente creado exitosamente',
            'data' => $subUser,
        ], 201);
    }

    /**
     * Ver detalles de un sub-usuario.
     */
    public function show($companyId, $id)
    {
        $company = auth()->user()->companies()->findOrFail($companyId);
        $subUser = $company->subUsers()->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $subUser,
        ]);
    }

    /**
     * Actualizar sub-usuario.
     */
    public function update(Request $request, $companyId, $id)
    {
        $company = auth()->user()->companies()->findOrFail($companyId);
        $subUser = $company->subUsers()->findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:sub_users,email,' . $subUser->id,
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'document_number' => 'nullable|string|max:20',
            'is_active' => 'sometimes|boolean',
        ]);

        $data = $request->except('password');
        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $subUser->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Cliente actualizado exitosamente',
            'data' => $subUser,
        ]);
    }

    /**
     * Eliminar sub-usuario.
     */
    public function destroy($companyId, $id)
    {
        $company = auth()->user()->companies()->findOrFail($companyId);
        $subUser = $company->subUsers()->findOrFail($id);
        
        $subUser->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cliente eliminado exitosamente',
        ]);
    }

    /**
     * Activar/desactivar sub-usuario.
     */
    public function toggleStatus($companyId, $id)
    {
        $company = auth()->user()->companies()->findOrFail($companyId);
        $subUser = $company->subUsers()->findOrFail($id);

        $subUser->update([
            'is_active' => !$subUser->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => $subUser->is_active ? 'Cliente activado' : 'Cliente desactivado',
            'data' => $subUser,
        ]);
    }

    /**
     * Restablecer contraseña de sub-usuario.
     */
    public function resetPassword(Request $request, $companyId, $id)
    {
        $company = auth()->user()->companies()->findOrFail($companyId);
        $subUser = $company->subUsers()->findOrFail($id);

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $subUser->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contraseña actualizada exitosamente',
        ]);
    }

    /**
     * Estadísticas del sub-usuario.
     */
    public function statistics($companyId, $id)
    {
        $company = auth()->user()->companies()->findOrFail($companyId);
        $subUser = $company->subUsers()->findOrFail($id);

        $stats = [
            'total_files_uploaded' => $subUser->uploadedFiles()->count(),
            'total_storage_used' => $subUser->uploadedFiles()->sum('size'),
            'last_upload' => $subUser->uploadedFiles()->latest()->first()?->created_at,
            'files_by_type' => $subUser->uploadedFiles()
                ->selectRaw('file_type, COUNT(*) as count')
                ->groupBy('file_type')
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
