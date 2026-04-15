<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArchivoImportado extends Model
{
    protected $table = 'archivos_importados';

    public $timestamps = false;

    protected $fillable = [
        'lote_id',
        'nombre_archivo',
        'nombre_original',
        'tipo_archivo',
        'tamanio_bytes',
        'total_registros',
        'registros_validos',
        'registros_invalidos',
        'errores',
        'estado',
        'importado_por',
        'fecha_importacion',
    ];

    protected $casts = [
        'tamanio_bytes' => 'integer',
        'total_registros' => 'integer',
        'registros_validos' => 'integer',
        'registros_invalidos' => 'integer',
        'fecha_importacion' => 'datetime',
    ];

    // Relación con lote
    public function lote()
    {
        return $this->belongsTo(LoteEmision::class, 'lote_id');
    }
}
