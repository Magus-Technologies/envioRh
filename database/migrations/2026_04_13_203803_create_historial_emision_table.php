<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_emision', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lote_id')->constrained('lote_emision')->onDelete('cascade');
            $table->enum('accion', [
                'lote_creado',
                'datos_importados',
                'recibo_agregado',
                'recibo_editado',
                'archivo_generado',
                'archivo_descargado',
                'subido_a_sunat',
                'emision_completada',
                'error_emision',
                'lote_cancelado',
                'lote_anulado'
            ]);
            $table->text('descripcion')->nullable()->comment('Descripción detallada de la acción');
            $table->string('usuario', 100)->nullable()->comment('Usuario que realizó la acción');
            $table->json('datos_adicionales')->nullable()->comment('Datos adicionales en formato JSON');
            $table->timestamp('fecha_accion')->useCurrent();

            $table->index('lote_id');
            $table->index('accion');
            $table->index('fecha_accion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_emision');
    }
};
