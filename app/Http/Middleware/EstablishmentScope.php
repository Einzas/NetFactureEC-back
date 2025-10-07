<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EstablishmentScope
{
    /**
     * Handle an incoming request.
     *
     * This middleware ensures that users can only access data from their own establishment.
     * SuperAdmin users bypass this restriction.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Si el usuario es superadmin, puede ver todo
        if ($user && $user->hasRole('superadmin')) {
            return $next($request);
        }

        // Si el usuario no tiene establecimiento asignado, denegar acceso
        if ($user && !$user->establishment_id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes un establecimiento asignado. Contacta al administrador.',
            ], 403);
        }

        // Agregar el establishment_id a los parámetros de la petición
        // Esto se puede usar en los controladores para filtrar automáticamente
        $request->merge([
            'scoped_establishment_id' => $user->establishment_id,
        ]);

        return $next($request);
    }
}
