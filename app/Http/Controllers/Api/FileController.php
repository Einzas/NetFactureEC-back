<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\File\UploadFileRequest;
use App\Http\Resources\FileResource;
use App\Models\UploadedFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    /**
     * Display a listing of the user's uploaded files.
     */
    public function index(Request $request): JsonResponse
    {
        $query = UploadedFile::where('user_id', Auth::id())
            ->with('user:id,name,email');

        // Filtrar por tipo de archivo
        if ($request->has('file_type')) {
            $query->where('file_type', $request->file_type);
        }

        // Filtrar archivos de sesión
        if ($request->has('session_only') && $request->session_only) {
            $query->whereNotNull('session_id');
        }

        // Excluir archivos expirados
        if ($request->has('exclude_expired') && $request->exclude_expired) {
            $query->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
        }

        $files = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => FileResource::collection($files),
            'pagination' => [
                'total' => $files->total(),
                'per_page' => $files->perPage(),
                'current_page' => $files->currentPage(),
                'last_page' => $files->lastPage(),
            ],
        ]);
    }

    /**
     * Store a newly uploaded file.
     */
    public function store(UploadFileRequest $request): JsonResponse
    {
        try {
            $file = $request->file('file');
            $fileType = $request->input('file_type');
            $userId = Auth::id();

            // Generar nombre único para el archivo
            $storedName = Str::uuid() . '.' . $file->getClientOriginalExtension();
            
            // Guardar en storage/app/uploads/user_{id}/
            $filePath = $file->storeAs(
                "uploads/user_{$userId}/{$fileType}",
                $storedName,
                'local'
            );

            // Calcular fecha de expiración
            $expiresAt = null;
            if ($request->has('expires_in_minutes')) {
                $expiresAt = now()->addMinutes($request->expires_in_minutes);
            }

            // Crear registro en base de datos
            $uploadedFile = UploadedFile::create([
                'user_id' => $userId,
                'establishment_id' => Auth::user()->establishment_id,
                'original_name' => $file->getClientOriginalName(),
                'stored_name' => $storedName,
                'file_path' => $filePath,
                'mime_type' => $file->getMimeType(),
                'extension' => $file->getClientOriginalExtension(),
                'size' => $file->getSize(),
                'file_type' => $fileType,
                'expires_at' => $expiresAt,
                'session_id' => $request->is_session_file ? session()->getId() : null,
                'description' => $request->description,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Archivo subido exitosamente',
                'data' => new FileResource($uploadedFile->load('user')),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al subir el archivo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified file information.
     */
    public function show(string $id): JsonResponse
    {
        $file = UploadedFile::where('id', $id)
            ->where('user_id', Auth::id())
            ->with('user:id,name,email')
            ->first();

        if (!$file) {
            return response()->json([
                'success' => false,
                'message' => 'Archivo no encontrado',
            ], 404);
        }

        // Verificar si el archivo ha expirado
        if ($file->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'El archivo ha expirado',
                'data' => new FileResource($file),
            ], 410); // 410 Gone
        }

        return response()->json([
            'success' => true,
            'data' => new FileResource($file),
        ]);
    }

    /**
     * Download the specified file.
     */
    public function download(string $id): StreamedResponse|JsonResponse
    {
        $file = UploadedFile::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$file) {
            return response()->json([
                'success' => false,
                'message' => 'Archivo no encontrado',
            ], 404);
        }

        // Verificar si el archivo ha expirado
        if ($file->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'El archivo ha expirado y no está disponible para descarga',
            ], 410);
        }

        // Verificar que el archivo existe en storage
        if (!$file->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'El archivo no existe en el almacenamiento',
            ], 404);
        }

        return Storage::disk('local')->download(
            $file->file_path,
            $file->original_name
        );
    }

    /**
     * Remove the specified file.
     */
    public function destroy(string $id): JsonResponse
    {
        $file = UploadedFile::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$file) {
            return response()->json([
                'success' => false,
                'message' => 'Archivo no encontrado',
            ], 404);
        }

        try {
            // El método deleteFile() se llama automáticamente por el evento deleting del modelo
            $file->delete();

            return response()->json([
                'success' => true,
                'message' => 'Archivo eliminado exitosamente',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el archivo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete expired files for the authenticated user.
     */
    public function cleanExpired(): JsonResponse
    {
        try {
            $expiredFiles = UploadedFile::where('user_id', Auth::id())
                ->whereNotNull('expires_at')
                ->where('expires_at', '<=', now())
                ->get();

            $count = $expiredFiles->count();

            foreach ($expiredFiles as $file) {
                $file->delete();
            }

            return response()->json([
                'success' => true,
                'message' => "Se eliminaron {$count} archivo(s) expirado(s)",
                'deleted_count' => $count,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al limpiar archivos expirados',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete all session files for the authenticated user.
     */
    public function cleanSessionFiles(): JsonResponse
    {
        try {
            $sessionFiles = UploadedFile::where('user_id', Auth::id())
                ->whereNotNull('session_id')
                ->get();

            $count = $sessionFiles->count();

            foreach ($sessionFiles as $file) {
                $file->delete();
            }

            return response()->json([
                'success' => true,
                'message' => "Se eliminaron {$count} archivo(s) de sesión",
                'deleted_count' => $count,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al limpiar archivos de sesión',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
