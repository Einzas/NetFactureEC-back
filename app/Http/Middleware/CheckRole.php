<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Verificar que el empleado tenga el rol requerido
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $employee = auth('employee')->user();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado.',
            ], 401);
        }

        if (!$employee->hasRole($role)) {
            return response()->json([
                'success' => false,
                'message' => "No tienes el rol requerido para esta acción. Se requiere: {$role}",
                'required_role' => $role,
            ], 403);
        }

        return $next($request);
    }
}
