<?php

namespace App\Console\Commands;

use App\Models\UploadedFile;
use Illuminate\Console\Command;

class CleanExpiredFilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'files:clean-expired 
                            {--force : Force deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean expired files from storage and database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Buscando archivos expirados...');

        $expiredFiles = UploadedFile::whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        if ($expiredFiles->count() === 0) {
            $this->info('✅ No se encontraron archivos expirados');
            return Command::SUCCESS;
        }

        $this->warn("⚠️  Se encontraron {$expiredFiles->count()} archivo(s) expirado(s)");

        // Mostrar tabla con archivos a eliminar
        $this->table(
            ['ID', 'Usuario', 'Archivo', 'Tipo', 'Expiró'],
            $expiredFiles->map(function ($file) {
                return [
                    $file->id,
                    $file->user->email,
                    $file->original_name,
                    $file->file_type,
                    $file->expires_at->diffForHumans(),
                ];
            })
        );

        // Confirmar eliminación si no se usa --force
        if (!$this->option('force')) {
            if (!$this->confirm('¿Desea eliminar estos archivos?', true)) {
                $this->info('Operación cancelada');
                return Command::SUCCESS;
            }
        }

        $deletedCount = 0;
        $errorCount = 0;

        $this->withProgressBar($expiredFiles, function ($file) use (&$deletedCount, &$errorCount) {
            try {
                $file->delete();
                $deletedCount++;
            } catch (\Exception $e) {
                $errorCount++;
                $this->error("\nError al eliminar archivo ID {$file->id}: {$e->getMessage()}");
            }
        });

        $this->newLine(2);
        $this->info("✅ Se eliminaron {$deletedCount} archivo(s) exitosamente");

        if ($errorCount > 0) {
            $this->error("❌ {$errorCount} archivo(s) no pudieron ser eliminados");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
