<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE historial_emision MODIFY COLUMN accion ENUM(
            'lote_creado',
            'datos_importados',
            'recibo_agregado',
            'recibo_editado',
            'recibo_emitido',
            'archivo_generado',
            'archivo_descargado',
            'subido_a_sunat',
            'emision_completada',
            'error_emision',
            'lote_cancelado',
            'lote_anulado'
        ) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE historial_emision MODIFY COLUMN accion ENUM(
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
        ) NOT NULL");
    }
};
