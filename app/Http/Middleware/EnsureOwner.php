<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwner
{
    /**
     * Verificar que el usuario autenticado sea owner
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('owner')->user();

        if (!$user || !$user->isOwner()) {
            return response()->json([
                'success' => false,
                'message' => 'Acceso no autorizado. Se requieren permisos de propietario.',
            ], 403);
        }

        return $next($request);
    }
}
