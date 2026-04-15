<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo_documento', ['DNI', 'RUC', 'CE', 'Pasaporte']);
            $table->string('numero_documento', 20);
            $table->string('nombre_razon_social', 255);
            $table->string('direccion', 255)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('actividad_economica', 255)->nullable()->comment('Descripción de la actividad del cliente');
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();

            $table->unique(['tipo_documento', 'numero_documento']);
            $table->index('numero_documento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
