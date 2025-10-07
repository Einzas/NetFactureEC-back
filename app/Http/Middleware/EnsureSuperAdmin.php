<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    /**
     * Verificar que el usuario autenticado sea superadministrador
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('superadmin')->user();

        if (!$user || !$user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Acceso no autorizado. Se requieren permisos de superadministrador.',
            ], 403);
        }

        return $next($request);
    }
}
