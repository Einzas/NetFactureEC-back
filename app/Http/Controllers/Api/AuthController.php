<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Register a new user
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'is_active' => true,
            ]);

            // Assign default role
            $user->assignRole('user');

            $token = JWTAuth::fromUser($user);

            return response()->json([
                'success' => true,
                'message' => 'Usuario registrado exitosamente',
                'data' => [
                    'user' => new UserResource($user->load('roles', 'permissions')),
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth('api')->factory()->getTTL() * 60
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login user and return JWT token
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        try {
            if (!$token = auth('api')->attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credenciales inválidas'
                ], 401);
            }

            $user = auth('api')->user();

            if (!$user->is_active) {
                auth('api')->logout();
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario inactivo. Contacte al administrador'
                ], 403);
            }

            // Update login information
            $user->updateLoginInfo($request->ip());

            // Cargar datos del establishment si existe
            $responseData = [
                'user' => new UserResource($user->load('roles', 'permissions')),
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60
            ];

            // Agregar datos del establishment si el usuario pertenece a uno
            if ($user->establishment_id) {
                $user->load('establishment');
                $responseData['establishment'] = [
                    'id' => $user->establishment->id,
                    'ruc' => $user->establishment->ruc,
                    'business_name' => $user->establishment->business_name,
                    'trade_name' => $user->establishment->trade_name,
                    'address' => $user->establishment->address,
                    'phone' => $user->establishment->phone,
                    'email' => $user->establishment->email,
                    'establishment_code' => $user->establishment->establishment_code,
                    'emission_point' => $user->establishment->emission_point,
                    'environment' => $user->establishment->environment,
                    'is_active' => $user->establishment->is_active,
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Login exitoso',
                'data' => $responseData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en el login',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get authenticated user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = auth('api')->user();
            
            return response()->json([
                'success' => true,
                'data' => new UserResource($user->load('roles', 'permissions'))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout user (Invalidate token)
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        try {
            auth('api')->logout();

            return response()->json([
                'success' => true,
                'message' => 'Logout exitoso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al hacer logout',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refresh JWT token
     *
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        try {
            $newToken = auth('api')->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Token actualizado',
                'data' => [
                    'token' => $newToken,
                    'token_type' => 'bearer',
                    'expires_in' => auth('api')->factory()->getTTL() * 60
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al refrescar token',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

