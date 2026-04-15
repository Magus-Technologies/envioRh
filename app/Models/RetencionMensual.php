<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RetencionMensual extends Model
{
    protected $table = 'retenciones_mensuales';

    public $timestamps = false;

    protected $fillable = [
        'emisor_numero_documento',
        'periodo_mes',
        'periodo_anio',
        'monto_acumulado',
        'retencion_acumulada',
        'supera_tope',
        'tope_mensual',
    ];

    protected $casts = [
        'periodo_mes' => 'integer',
        'periodo_anio' => 'integer',
        'monto_acumulado' => 'decimal:2',
        'retencion_acumulada' => 'decimal:2',
        'tope_mensual' => 'decimal:2',
        'supera_tope' => 'boolean',
    ];

    // Scope por emisor y período
    public function scopePorEmisorYPeriodo($query, $documento, $mes, $anio)
    {
        return $query->where('emisor_numero_documento', $documento)
                     ->where('periodo_mes', $mes)
                     ->where('periodo_anio', $anio);
    }
}
