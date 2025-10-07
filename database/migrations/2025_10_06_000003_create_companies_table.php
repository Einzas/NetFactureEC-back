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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            
            // Datos fiscales
            $table->string('ruc', 13)->unique()->comment('RUC Ecuador (13 dígitos)');
            $table->string('business_name')->comment('Razón social');
            $table->string('trade_name')->nullable()->comment('Nombre comercial');
            
            // Contacto
            $table->string('address', 500);
            $table->string('city', 100)->default('Quito');
            $table->string('province', 100)->default('Pichincha');
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();

            // Estado y límites
            $table->boolean('is_active')->default(true);
           
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['owner_id', 'is_active']);
            $table->index('ruc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
