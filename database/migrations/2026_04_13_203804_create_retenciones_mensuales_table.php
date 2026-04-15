<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retenciones_mensuales', function (Blueprint $table) {
            $table->id();
            $table->string('emisor_numero_documento', 20);
            $table->unsignedTinyInteger('periodo_mes');
            $table->unsignedSmallInteger('periodo_anio');
            $table->decimal('monto_acumulado', 12, 2)->default(0)->comment('Monto bruto acumulado en el mes');
            $table->decimal('retencion_acumulada', 12, 2)->default(0)->comment('Retención acumulada');
            $table->boolean('supera_tope')->default(false)->comment('¿Superó el tope de exoneración?');
            $table->decimal('tope_mensual', 12, 2)->default(1500)->comment('Tope mensual exonerado (S/ 1,500)');
            $table->timestamps();

            $table->unique(['emisor_numero_documento', 'periodo_mes', 'periodo_anio'], 'uk_emisor_periodo');
            $table->index('emisor_numero_documento');
            $table->index(['periodo_mes', 'periodo_anio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retenciones_mensuales');
    }
};
