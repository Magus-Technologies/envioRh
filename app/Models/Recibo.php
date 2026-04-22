<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recibo extends Model
{
    protected $table = 'recibos';

    protected $fillable = [
        'lote_id',
        'cliente_id',
        'emisor_tipo_documento',
        'emisor_numero_documento',
        'emisor_nombre',
        'descripcion_servicio',
        'fecha_emision',
        'fecha_vencimiento',
        'monto_bruto',
        'aplica_retencion',
        'porcentaje_retencion',
        'monto_retencion',
        'monto_neto',
        'moneda',
        'numero_continuacion',
        'numero_recibo_sunat',
        'archivo_pdf',
        'fecha_procesado',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'fecha_procesado' => 'datetime',
        'monto_bruto' => 'decimal:2',
        'monto_retencion' => 'decimal:2',
        'monto_neto' => 'decimal:2',
        'aplica_retencion' => 'boolean',
        'porcentaje_retencion' => 'decimal:2',
    ];

    // Relación con lote
    public function lote()
    {
        return $this->belongsTo(LoteEmision::class, 'lote_id');
    }

    // Relación con cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    // Scope para estado
    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    // Scope por emisor
    public function scopePorEmisor($query, $documento)
    {
        return $query->where('emisor_numero_documento', $documento);
    }
}
