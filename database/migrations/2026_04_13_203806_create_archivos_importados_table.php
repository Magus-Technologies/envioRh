<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('archivos_importados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lote_id')->nullable()->constrained('lote_emision')->onDelete('set null');
            $table->string('nombre_archivo', 255);
            $table->string('nombre_original', 255);
            $table->enum('tipo_archivo', ['excel', 'csv', 'txt']);
            $table->unsignedInteger('tamanio_bytes')->nullable();
            $table->unsignedInteger('total_registros')->default(0);
            $table->unsignedInteger('registros_validos')->default(0);
            $table->unsignedInteger('registros_invalidos')->default(0);
            $table->text('errores')->nullable()->comment('Lista de errores encontrados');
            $table->enum('estado', ['importado', 'parcial', 'error'])->default('importado');
            $table->string('importado_por', 100)->nullable();
            $table->timestamp('fecha_importacion')->useCurrent();

            $table->index('lote_id');
            $table->index('fecha_importacion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archivos_importados');
    }
};
