<?php

namespace App\Http\Middleware;

use App\Models\UploadedFile;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CleanExpiredFiles
{
    /**
     * Handle an incoming request.
     * 
     * Este middleware limpia archivos expirados periódicamente.
     * Se ejecuta con una probabilidad del 10% en cada request para no sobrecargar el sistema.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Ejecutar limpieza solo el 10% de las veces (random)
        if (rand(1, 10) === 1) {
            $this->cleanExpiredFiles();
        }

        return $next($request);
    }

    /**
     * Clean expired files from database and storage.
     */
    protected function cleanExpiredFiles(): void
    {
        try {
            $expiredFiles = UploadedFile::whereNotNull('expires_at')
                ->where('expires_at', '<=', now())
                ->get();

            if ($expiredFiles->count() > 0) {
                foreach ($expiredFiles as $file) {
                    $file->delete(); // El evento deleting del modelo eliminará el archivo físico
                }

                Log::info("Limpieza automática: {$expiredFiles->count()} archivo(s) expirado(s) eliminado(s)");
            }

        } catch (\Exception $e) {
            Log::error('Error en limpieza automática de archivos: ' . $e->getMessage());
        }
    }
}
