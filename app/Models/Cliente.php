<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'clientes';

    protected $fillable = [
        'tipo_documento',
        'numero_documento',
        'nombre_razon_social',
        'direccion',
        'email',
        'telefono',
        'actividad_economica',
        'estado',
    ];

    protected $casts = [
        'estado' => 'string',
    ];

    // Relación con recibos
    public function recibos()
    {
        return $this->hasMany(Recibo::class);
    }
}
