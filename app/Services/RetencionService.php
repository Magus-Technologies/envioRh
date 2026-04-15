<?php

namespace App\Services;

use App\Models\RetencionMensual;
use App\Models\Recibo;
use Carbon\Carbon;

class RetencionService
{
    /**
     * Tope mensual exonerado (S/ 1,500 por defecto)
     */
    protected float $topeMensual;

    /**
     * Porcentaje de retención (8% por defecto)
     */
    protected float $porcentajeRetencion;

    public function __construct()
    {
        $this->topeMensual = (float) config('app.tope_mensual_exonerado', 1500);
        $this->porcentajeRetencion = (float) config('app.retencion_porcentaje', 8);
    }

    /**
     * Calcular retención para un recibo
     * 
     * @param string $emisorDocumento Documento del emisor
     * @param Carbon $fechaEmision Fecha de emisión
     * @param float $montoBruto Monto bruto del servicio
     * @return array ['aplica_retencion' => bool, 'monto_retencion' => float, 'monto_neto' => float]
     */
    public function calcularRetencion(string $emisorDocumento, Carbon $fechaEmision, float $montoBruto): array
    {
        $mes = $fechaEmision->month;
        $anio = $fechaEmision->year;

        // Obtener acumulado del mes para este emisor
        $acumulado = $this->obtenerAcumuladoMensual($emisorDocumento, $mes, $anio);

        // Verificar si supera el tope
        $superaTope = ($acumulado + $montoBruto) > $this->topeMensual;

        if ($superaTope) {
            $montoRetencion = $montoBruto * ($this->porcentajeRetencion / 100);
            $montoNeto = $montoBruto - $montoRetencion;
            
            return [
                'aplica_retencion' => true,
                'monto_retencion' => round($montoRetencion, 2),
                'monto_neto' => round($montoNeto, 2),
            ];
        }

        return [
            'aplica_retencion' => false,
            'monto_retencion' => 0.00,
            'monto_neto' => round($montoBruto, 2),
        ];
    }

    /**
     * Obtener acumulado mensual de un emisor
     */
    protected function obtenerAcumuladoMensual(string $documento, int $mes, int $anio): float
    {
        $retencion = RetencionMensual::query()
            ->where('emisor_numero_documento', $documento)
            ->where('periodo_mes', $mes)
            ->where('periodo_anio', $anio)
            ->first();

        return $retencion ? (float) $retencion->monto_acumulado : 0.0;
    }

    /**
     * Actualizar acumulado mensual
     */
    public function actualizarAcumulado(string $documento, Carbon $fechaEmision, float $montoBruto, float $montoRetencion): void
    {
        $mes = $fechaEmision->month;
        $anio = $fechaEmision->year;

        $retencion = RetencionMensual::updateOrCreate(
            [
                'emisor_numero_documento' => $documento,
                'periodo_mes' => $mes,
                'periodo_anio' => $anio,
            ],
            [
                'monto_acumulado' => \DB::raw('COALESCE(monto_acumulado, 0) + ' . $montoBruto),
                'retencion_acumulada' => \DB::raw('COALESCE(retencion_acumulada, 0) + ' . $montoRetencion),
            ]
        );

        // Verificar si supera el tope
        $retencion->supera_tope = (float) $retencion->monto_acumulado > $this->topeMensual;
        $retencion->save();
    }

    /**
     * Verificar si un emisor supera el tope en un mes
     */
    public function verificaSuperaTope(string $documento, int $mes, int $anio): bool
    {
        $retencion = RetencionMensual::query()
            ->where('emisor_numero_documento', $documento)
            ->where('periodo_mes', $mes)
            ->where('periodo_anio', $anio)
            ->first();

        return $retencion ? (bool) $retencion->supera_tope : false;
    }

    /**
     * Obtener tope mensual
     */
    public function getTopeMensual(): float
    {
        return $this->topeMensual;
    }

    /**
     * Obtener porcentaje de retención
     */
    public function getPorcentajeRetencion(): float
    {
        return $this->porcentajeRetencion;
    }
}
