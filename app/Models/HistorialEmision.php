<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialEmision extends Model
{
    protected $table = 'historial_emision';

    public $timestamps = false;

    protected $fillable = [
        'lote_id',
        'accion',
        'descripcion',
        'usuario',
        'datos_adicionales',
        'fecha_accion',
    ];

    protected $casts = [
        'datos_adicionales' => 'array',
        'fecha_accion' => 'datetime',
    ];

    // Relación con lote
    public function lote()
    {
        return $this->belongsTo(LoteEmision::class, 'lote_id');
    }

    // Scope por acción
    public function scopePorAccion($query, $accion)
    {
        return $query->where('accion', $accion);
    }
}
