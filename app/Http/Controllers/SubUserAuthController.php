<?php

namespace App\Http\Controllers;

use App\Models\SubUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SubUserAuthController extends Controller
{
    /**
     * Login de sub-usuario (cliente).
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $subUser = SubUser::where('email', $request->email)->first();

        if (!$subUser || !Hash::check($request->password, $subUser->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }

        if (!$subUser->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Tu cuenta está inactiva. Contacta al administrador.',
            ], 403);
        }

        if (!$subUser->company->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'La empresa está inactiva. Contacta al administrador.',
            ], 403);
        }

        // Actualizar información de login
        $subUser->updateLoginInfo($request->ip());

        // Generar token JWT
        $token = auth('sub_user')->login($subUser);

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'sub_user' => [
                    'id' => $subUser->id,
                    'name' => $subUser->name,
                    'email' => $subUser->email,
                    'company' => [
                        'id' => $subUser->company->id,
                        'business_name' => $subUser->company->business_name,
                        'ruc' => $subUser->company->ruc,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Obtener información del sub-usuario autenticado.
     */
    public function me()
    {
        $subUser = auth('sub_user')->user();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $subUser->id,
                'name' => $subUser->name,
                'email' => $subUser->email,
                'phone' => $subUser->phone,
                'document_number' => $subUser->document_number,
                'company' => [
                    'id' => $subUser->company->id,
                    'business_name' => $subUser->company->business_name,
                    'trade_name' => $subUser->company->trade_name,
                    'ruc' => $subUser->company->ruc,
                    'address' => $subUser->company->address,
                    'phone' => $subUser->company->phone,
                    'email' => $subUser->company->email,
                ],
            ],
        ]);
    }

    /**
     * Logout de sub-usuario.
     */
    public function logout()
    {
        auth('sub_user')->logout();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada exitosamente',
        ]);
    }

    /**
     * Refrescar token JWT.
     */
    public function refresh()
    {
        $token = auth('sub_user')->refresh();

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
            ],
        ]);
    }
}
