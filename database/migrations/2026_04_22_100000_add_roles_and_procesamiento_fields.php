<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('rol', ['admin', 'cliente'])->default('cliente')->after('password');
        });

        Schema::table('lote_emision', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('creado_por')->constrained('users')->nullOnDelete();
            $table->index('user_id');
        });

        Schema::table('recibos', function (Blueprint $table) {
            $table->string('archivo_pdf', 500)->nullable()->after('numero_recibo_sunat')->comment('Ruta del PDF emitido por SUNAT y subido por admin');
            $table->timestamp('fecha_procesado')->nullable()->after('archivo_pdf');
        });

        DB::statement("ALTER TABLE recibos MODIFY COLUMN estado ENUM(
            'pendiente',
            'validado',
            'generado',
            'en_cola',
            'emitido',
            'anulado',
            'error'
        ) NOT NULL DEFAULT 'pendiente'");

        DB::statement("ALTER TABLE historial_emision MODIFY COLUMN accion ENUM(
            'lote_creado',
            'datos_importados',
            'recibo_agregado',
            'recibo_editado',
            'recibo_emitido',
            'enviado_a_procesamiento',
            'procesado_por_admin',
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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('rol');
        });

        Schema::table('lote_emision', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('recibos', function (Blueprint $table) {
            $table->dropColumn(['archivo_pdf', 'fecha_procesado']);
        });

        DB::statement("ALTER TABLE recibos MODIFY COLUMN estado ENUM(
            'pendiente','validado','generado','emitido','anulado','error'
        ) NOT NULL DEFAULT 'pendiente'");

        DB::statement("ALTER TABLE historial_emision MODIFY COLUMN accion ENUM(
            'lote_creado','datos_importados','recibo_agregado','recibo_editado',
            'recibo_emitido','archivo_generado','archivo_descargado','subido_a_sunat',
            'emision_completada','error_emision','lote_cancelado','lote_anulado'
        ) NOT NULL");
    }
};
