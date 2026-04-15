<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $table = 'usuarios';

    protected $fillable = [
        'nombres',
        'apellidos',
        'email',
        'password_hash',
        'rol',
        'estado',
        'ultimo_acceso',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $casts = [
        'ultimo_acceso' => 'datetime',
    ];

    // Verificar si es admin
    public function isAdmin(): bool
    {
        return $this->rol === 'admin';
    }

    // Verificar si está activo
    public function estaActivo(): bool
    {
        return $this->estado === 'activo';
    }
}
