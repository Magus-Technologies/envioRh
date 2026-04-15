<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_empresa', function (Blueprint $table) {
            $table->id();
            $table->string('ruc_emisor', 11)->unique();
            $table->string('razon_social', 255);
            $table->string('direccion', 255)->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('clave_sol_usuario', 100)->nullable()->comment('Usuario SOL para acceso a SUNAT');
            $table->enum('estado', ['activa', 'inactiva'])->default('activa');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_empresa');
    }
};
