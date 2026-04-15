<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lote_emision', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_lote', 50)->unique()->comment('Código único del lote (ej: LOTE-202604-001)');
            $table->unsignedTinyInteger('periodo_mes')->comment('Mes del periodo (1-12)');
            $table->unsignedSmallInteger('periodo_anio')->comment('Año del periodo');
            $table->string('descripcion', 255)->nullable()->comment('Descripción del lote');
            $table->unsignedInteger('total_recibos')->default(0)->comment('Cantidad de recibos en el lote');
            $table->decimal('monto_total', 12, 2)->default(0)->comment('Suma de montos de recibos');
            $table->decimal('retencion_total', 12, 2)->default(0)->comment('Suma de retenciones');
            $table->decimal('neto_total', 12, 2)->default(0)->comment('Suma de montos netos');
            $table->string('archivo_generado', 255)->nullable()->comment('Nombre del archivo generado para SUNAT');
            $table->string('archivo_ruta', 500)->nullable()->comment('Ruta del archivo generado');
            $table->enum('estado', ['pendiente', 'generado', 'subido_sunat', 'emitido', 'error', 'cancelado'])->default('pendiente');
            $table->timestamp('fecha_generacion')->nullable()->comment('Fecha cuando se generó el archivo');
            $table->timestamp('fecha_subida')->nullable()->comment('Fecha cuando se subió a SUNAT');
            $table->timestamp('fecha_emision')->nullable()->comment('Fecha cuando SUNAT emitió los recibos');
            $table->foreignId('creado_por')->nullable()->comment('ID del usuario que creó el lote');
            $table->timestamps();

            $table->index(['periodo_mes', 'periodo_anio']);
            $table->index('estado');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lote_emision');
    }
};
