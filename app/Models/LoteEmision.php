<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoteEmision extends Model
{
    protected $table = 'lote_emision';

    protected $fillable = [
        'codigo_lote',
        'periodo_mes',
        'periodo_anio',
        'descripcion',
        'total_recibos',
        'monto_total',
        'retencion_total',
        'neto_total',
        'archivo_generado',
        'archivo_ruta',
        'estado',
        'fecha_generacion',
        'fecha_subida',
        'fecha_emision',
        'creado_por',
        'user_id',
    ];

    protected $casts = [
        'periodo_mes' => 'integer',
        'periodo_anio' => 'integer',
        'total_recibos' => 'integer',
        'monto_total' => 'decimal:2',
        'retencion_total' => 'decimal:2',
        'neto_total' => 'decimal:2',
        'fecha_generacion' => 'datetime',
        'fecha_subida' => 'datetime',
        'fecha_emision' => 'datetime',
    ];

    // Relación con recibos
    public function recibos()
    {
        return $this->hasMany(Recibo::class, 'lote_id');
    }

    // Relación con historial
    public function historial()
    {
        return $this->hasMany(HistorialEmision::class, 'lote_id');
    }

    // Relación con archivos importados
    public function archivosImportados()
    {
        return $this->hasMany(ArchivoImportado::class, 'lote_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scope para estado
    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    // Scope por período
    public function scopePorPeriodo($query, $mes, $anio)
    {
        return $query->where('periodo_mes', $mes)
                     ->where('periodo_anio', $anio);
    }

    public function recalcularTotales(): void
    {
        $t = Recibo::where('lote_id', $this->id)
            ->selectRaw('COUNT(*) as total, COALESCE(SUM(monto_bruto),0) as bruto, COALESCE(SUM(monto_retencion),0) as retencion, COALESCE(SUM(monto_neto),0) as neto')
            ->first();

        $this->update([
            'total_recibos' => $t->total ?? 0,
            'monto_total' => $t->bruto ?? 0,
            'retencion_total' => $t->retencion ?? 0,
            'neto_total' => $t->neto ?? 0,
        ]);
    }
}
