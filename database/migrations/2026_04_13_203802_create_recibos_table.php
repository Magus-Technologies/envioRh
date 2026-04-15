<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recibos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lote_id')->constrained('lote_emision')->onDelete('restrict');
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('restrict');

            // Datos del emisor (persona que presta el servicio)
            $table->enum('emisor_tipo_documento', ['DNI', 'CE', 'Pasaporte'])->default('DNI');
            $table->string('emisor_numero_documento', 20)->comment('Documento del emisor');
            $table->string('emisor_nombre', 255)->comment('Nombre completo del emisor');

            // Datos del recibo
            $table->text('descripcion_servicio')->comment('Descripción del servicio prestado');
            $table->date('fecha_emision')->comment('Fecha de emisión del recibo');
            $table->date('fecha_vencimiento')->nullable()->comment('Fecha de vencimiento del pago');

            // Montos
            $table->decimal('monto_bruto', 12, 2)->comment('Monto antes de retención');
            $table->boolean('aplica_retencion')->default(false)->comment('¿Aplica retención 4ta categoría?');
            $table->decimal('porcentaje_retencion', 5, 2)->default(8.00)->comment('Porcentaje de retención (8%)');
            $table->decimal('monto_retencion', 12, 2)->default(0)->comment('Monto de la retención calculada');
            $table->decimal('monto_neto', 12, 2)->comment('Monto después de retención');

            // Moneda
            $table->enum('moneda', ['PEN', 'USD'])->default('PEN')->comment('Tipo de moneda');

            // Número de continuación (opcional, para uso interno)
            $table->string('numero_continuacion', 50)->nullable()->comment('Número de referencia interna');

            // Estado y trazabilidad
            $table->string('numero_recibo_sunat', 50)->nullable()->comment('Número asignado por SUNAT tras emisión');
            $table->enum('estado', ['pendiente', 'validado', 'generado', 'emitido', 'anulado', 'error'])->default('pendiente');
            $table->text('observaciones')->nullable()->comment('Observaciones o notas');

            $table->timestamps();

            $table->index('lote_id');
            $table->index('cliente_id');
            $table->index('fecha_emision');
            $table->index('estado');
            $table->index('emisor_numero_documento');
            $table->index('numero_recibo_sunat');
            $table->index(['lote_id', 'estado']);
            $table->index(['emisor_numero_documento', 'fecha_emision']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recibos');
    }
};
