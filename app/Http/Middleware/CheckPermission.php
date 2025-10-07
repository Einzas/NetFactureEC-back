<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Verificar que el empleado tenga el permiso requerido
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $employee = auth('employee')->user();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado.',
            ], 401);
        }

        if (!$employee->can($permission)) {
            return response()->json([
                'success' => false,
                'message' => "No tienes permiso para realizar esta acción. Se requiere: {$permission}",
                'required_permission' => $permission,
            ], 403);
        }

        return $next($request);
    }
}
