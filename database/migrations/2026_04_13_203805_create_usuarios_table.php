<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('email', 100)->unique();
            $table->string('password_hash', 255);
            $table->enum('rol', ['admin', 'operador', 'consulta'])->default('operador');
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamp('ultimo_acceso')->nullable();
            $table->timestamps();

            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
