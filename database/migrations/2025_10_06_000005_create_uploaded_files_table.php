<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('uploaded_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null')->comment('Owner que subió el archivo');
            
            // Información del archivo
            $table->string('original_name');
            $table->string('stored_name')->unique();
            $table->string('path');
            $table->string('mime_type');
            $table->bigInteger('size')->comment('Tamaño en bytes');
            $table->string('extension', 10);
            
            // Clasificación
            $table->enum('type', [
                'invoice',
                'credit_note',
                'debit_note',
                'withholding',
                'purchase_settlement',
                'document',
                'signature',
                'logo',
                'other'
            ])->default('other');
            
            $table->string('module', 50)->nullable()->comment('Módulo que subió el archivo');
            $table->unsignedBigInteger('related_id')->nullable()->comment('ID del registro relacionado');
            
            // Metadatos
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->string('hash', 64)->nullable()->comment('SHA256 para detección de duplicados');
            
            // Estado
            $table->boolean('is_public')->default(false);
            $table->boolean('is_processed')->default(false);
            $table->timestamp('processed_at')->nullable();
            $table->unsignedInteger('download_count')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['company_id', 'type']);
            $table->index(['module', 'related_id']);
            $table->index('hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uploaded_files');
    }
};
