<?php

namespace App\Http\Controllers;

use App\Models\Recibo;
use App\Models\LoteEmision;
use App\Models\RetencionMensual;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    /**
     * Reporte para PLAME
     */
    public function plame(Request $request)
    {
        $mes = $request->input('mes', date('m'));
        $anio = $request->input('anio', date('Y'));

        $recibos = Recibo::whereMonth('fecha_emision', $mes)
                         ->whereYear('fecha_emision', $anio)
                         ->where('estado', 'emitido')
                         ->with('cliente')
                         ->get();

        // Agrupar por emisor
        $porEmisor = $recibos->groupBy('emisor_numero_documento')->map(function($group) {
            return [
                'documento' => $group->first()->emisor_numero_documento,
                'nombre' => $group->first()->emisor_nombre,
                'cantidad_recibos' => $group->count(),
                'total_bruto' => $group->sum('monto_bruto'),
                'total_retencion' => $group->sum('monto_retencion'),
                'total_neto' => $group->sum('monto_neto'),
            ];
        });

        return view('reportes.plame', compact('porEmisor', 'mes', 'anio', 'recibos'));
    }

    /**
     * Reporte de retenciones mensuales
     */
    public function retenciones(Request $request)
    {
        $mes = $request->input('mes', date('m'));
        $anio = $request->input('anio', date('Y'));

        $retenciones = RetencionMensual::where('periodo_mes', $mes)
                                       ->where('periodo_anio', $anio)
                                       ->orderBy('monto_acumulado', 'desc')
                                       ->get();

        return view('reportes.retenciones', compact('retenciones', 'mes', 'anio'));
    }

    /**
     * Resumen de lotes
     */
    public function resumenLotes(Request $request)
    {
        $query = LoteEmision::query();

        if ($request->has('anio')) {
            $query->where('periodo_anio', $request->anio);
        }

        $lotes = $query->orderBy('periodo_anio', 'desc')
                       ->orderBy('periodo_mes', 'desc')
                       ->get();

        // Estadísticas generales
        $estadisticas = [
            'total_lotes' => $lotes->count(),
            'total_recibos' => $lotes->sum('total_recibos'),
            'monto_total' => $lotes->sum('monto_total'),
            'retencion_total' => $lotes->sum('retencion_total'),
            'neto_total' => $lotes->sum('neto_total'),
        ];

        return view('reportes.resumen_lotes', compact('lotes', 'estadisticas'));
    }

    /**
     * Exportar reporte a Excel
     */
    public function exportarExcel(Request $request)
    {
        $tipo = $request->input('tipo', 'plame');
        $mes = $request->input('mes', date('m'));
        $anio = $request->input('anio', date('Y'));

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        if ($tipo === 'plame') {
            $recibos = Recibo::whereMonth('fecha_emision', $mes)
                            ->whereYear('fecha_emision', $anio)
                            ->where('estado', 'emitido')
                            ->with('cliente')
                            ->get();

            // Encabezados
            $sheet->fromArray([
                ['Reporte PLAME - ' . date('d/m/Y', mktime(0, 0, 0, $mes, 1, $anio))]
            ], null, 'A1');

            $sheet->fromArray([
                ['Documento Emisor', 'Nombre Emisor', 'Documento Cliente', 'Nombre Cliente', 'Descripción', 'Monto Bruto', 'Retención', 'Monto Neto', 'Fecha Emisión']
            ], null, 'A3');

            $datos = [];
            foreach ($recibos as $recibo) {
                $datos[] = [
                    $recibo->emisor_numero_documento,
                    $recibo->emisor_nombre,
                    $recibo->cliente->numero_documento,
                    $recibo->cliente->nombre_razon_social,
                    $recibo->descripcion_servicio,
                    $recibo->monto_bruto,
                    $recibo->monto_retencion,
                    $recibo->monto_neto,
                    $recibo->fecha_emision->format('Y-m-d'),
                ];
            }

            $sheet->fromArray($datos, null, 'A4');
        }

        // Auto-size columns
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $nombreArchivo = "reporte_{$tipo}_{$anio}_{$mes}.xlsx";
        $tempFile = storage_path('app/' . $nombreArchivo);
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempFile);

        return response()->download($tempFile, $nombreArchivo)->deleteFileAfterSend(true);
    }
}
