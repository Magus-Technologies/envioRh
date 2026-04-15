<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionEmpresa extends Model
{
    protected $table = 'configuracion_empresa';

    protected $fillable = [
        'ruc_emisor',
        'razon_social',
        'direccion',
        'telefono',
        'email',
        'clave_sol_usuario',
        'estado',
    ];

    protected $casts = [
        'estado' => 'string',
    ];
}
