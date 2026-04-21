<?php

namespace App\Services;

use App\Models\Recibo;
use App\Models\LoteEmision;
use Illuminate\Support\Facades\Storage;

class SunatFileService
{
    /**
     * Generar archivo TXT para SUNAT (formato oficial)
     */
    public function generarArchivoTXT(LoteEmision $lote): string
    {
        $recibos = $lote->recibos()->where('estado', '!=', 'anulado')->get();

        if ($recibos->isEmpty()) {
            throw new \Exception('El lote no tiene recibos para generar el archivo');
        }

        $contenido = $this->generarContenidoTXT($recibos, $lote);

        // Guardar archivo
        $nombreArchivo = $this->generarNombreArchivo($lote);
        $ruta = 'exports/sunat/' . $nombreArchivo;

        Storage::disk('local')->put($ruta, $contenido);

        // Actualizar lote
        $lote->update([
            'archivo_generado' => $nombreArchivo,
            'archivo_ruta' => $ruta,
            'estado' => 'generado',
            'fecha_generacion' => now(),
        ]);

        return $ruta;
    }

    /**
     * Generar contenido del archivo TXT
     */
    protected function generarContenidoTXT($recibos, LoteEmision $lote): string
    {
        $lineas = [];

        // Primera línea: Encabezado
        // Usamos datos del primer recibo para el emisor
        $primerRecibo = $recibos->first();
        $tipoDocEmisor = $this->getTipoDocumentoCodigo($primerRecibo->emisor_tipo_documento);
        
        $lineas[] = sprintf(
            'E|%s|%s|%s|%s',
            $tipoDocEmisor,
            $primerRecibo->emisor_numero_documento,
            $primerRecibo->fecha_emision->format('Y-m-d'),
            strtoupper($primerRecibo->moneda)
        );

        // Líneas de detalle
        foreach ($recibos as $recibo) {
            $tipoDocReceptor = $this->getTipoDocumentoCodigo($recibo->cliente->tipo_documento);
            $descripcion = $this->sanearDescripcion($recibo->descripcion_servicio);
            $fechaVencimiento = $recibo->fecha_vencimiento ? $recibo->fecha_vencimiento->format('Y-m-d') : '';

            $lineas[] = sprintf(
                'D|%s|%s|%s|%s|%s',
                $tipoDocReceptor,
                $recibo->cliente->numero_documento,
                $descripcion,
                number_format($recibo->monto_bruto, 2, '.', ''),
                $fechaVencimiento
            );
        }

        return implode("\n", $lineas);
    }

    /**
     * Generar Excel masivo de recibos del lote, listo para emisión en SUNAT SOL
     */
    public function generarArchivoExcel(LoteEmision $lote): string
    {
        $recibos = $lote->recibos()->with('cliente')
            ->where('estado', '!=', 'anulado')
            ->orderBy('fecha_emision')
            ->orderBy('id')
            ->get();

        if ($recibos->isEmpty()) {
            throw new \Exception('El lote no tiene recibos para exportar');
        }

        $nombreArchivo = sprintf(
            'recibos_emision_%s_%s.xlsx',
            $lote->codigo_lote,
            now()->format('YmdHis')
        );
        $ruta = 'exports/sunat/' . $nombreArchivo;

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Recibos ' . $lote->codigo_lote);

        // Encabezados
        $encabezados = [
            '#',
            'Fecha emisión',
            'Tipo doc cliente',
            'N° documento cliente',
            'Nombre / Razón social',
            'Descripción del servicio',
            'Moneda',
            'Monto bruto',
            '¿Retención 8%?',
            'Monto retención',
            'Monto neto',
            'N° recibo SUNAT',
            'Estado',
        ];
        $sheet->fromArray([$encabezados], null, 'A1');

        // Datos
        $fila = 2;
        foreach ($recibos as $i => $recibo) {
            $sheet->fromArray([[
                $i + 1,
                \Carbon\Carbon::parse($recibo->fecha_emision)->format('d/m/Y'),
                $recibo->cliente->tipo_documento,
                $recibo->cliente->numero_documento,
                $recibo->cliente->nombre_razon_social,
                $recibo->descripcion_servicio,
                $recibo->moneda,
                number_format($recibo->monto_bruto, 2, '.', ''),
                $recibo->aplica_retencion ? 'SÍ' : 'NO',
                number_format($recibo->monto_retencion, 2, '.', ''),
                number_format($recibo->monto_neto, 2, '.', ''),
                $recibo->numero_recibo_sunat ?? '',
                strtoupper($recibo->estado),
            ]], null, 'A' . $fila);
            $fila++;
        }

        // Estilo encabezado
        $sheet->getStyle('A1:M1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '6B3410'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '4A2409'],
                ],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(38);

        // Fila totales
        $totalRow = $fila;
        $sheet->setCellValue('A' . $totalRow, 'TOTAL');
        $sheet->mergeCells('A' . $totalRow . ':G' . $totalRow);
        $sheet->setCellValue('H' . $totalRow, '=SUM(H2:H' . ($totalRow - 1) . ')');
        $sheet->setCellValue('J' . $totalRow, '=SUM(J2:J' . ($totalRow - 1) . ')');
        $sheet->setCellValue('K' . $totalRow, '=SUM(K2:K' . ($totalRow - 1) . ')');
        $sheet->getStyle('A' . $totalRow . ':M' . $totalRow)->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FBF5EC'],
            ],
            'borders' => [
                'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM],
            ],
        ]);
        $sheet->getStyle('A' . $totalRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('H2:K' . $totalRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('H2:K' . $totalRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        // Congelar encabezado y ajustar anchos
        $sheet->freezePane('A2');
        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Guardar archivo
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempFile = \Storage::disk('local')->path($ruta);

        $dir = dirname($tempFile);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        $writer->save($tempFile);

        $lote->update([
            'archivo_generado' => $nombreArchivo,
            'archivo_ruta' => $ruta,
            'estado' => 'generado',
            'fecha_generacion' => now(),
        ]);

        return $ruta;
    }

    /**
     * Generar nombre de archivo
     */
    protected function generarNombreArchivo(LoteEmision $lote): string
    {
        return sprintf(
            'RHE_%s_%s.txt',
            $lote->codigo_lote,
            now()->format('YmdHis')
        );
    }

    /**
     * Obtener código de tipo de documento
     */
    protected function getTipoDocumentoCodigo(string $tipoDocumento): string
    {
        $mapa = [
            'DNI' => '1',
            'CE' => '4',
            'RUC' => '6',
            'Pasaporte' => '7',
        ];

        return $mapa[$tipoDocumento] ?? '1';
    }

    /**
     * Sanear descripción (sin pipes)
     */
    protected function sanearDescripcion(string $descripcion): string
    {
        // Reemplazar pipes por guiones
        $descripcion = str_replace('|', '-', $descripcion);
        
        // Limitar a 500 caracteres
        return substr($descripcion, 0, 500);
    }

    /**
     * Descargar archivo
     */
    public function descargarArchivo(string $ruta): array
    {
        if (!Storage::disk('local')->exists($ruta)) {
            throw new \Exception('El archivo no existe');
        }

        $contenido = Storage::disk('local')->get($ruta);
        $nombreArchivo = basename($ruta);

        return [
            'nombre' => $nombreArchivo,
            'contenido' => $contenido,
            'tipo' => $this->getMimeType($ruta),
        ];
    }

    /**
     * Obtener MIME type
     */
    protected function getMimeType(string $ruta): string
    {
        $extension = pathinfo($ruta, PATHINFO_EXTENSION);
        
        return match ($extension) {
            'txt' => 'text/plain',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            default => 'application/octet-stream',
        };
    }

    /**
     * Validar datos antes de generar archivo
     */
    public function validarRecibos($recibos): array
    {
        $errores = [];

        foreach ($recibos as $index => $recibo) {
            $num = $index + 1;

            // Validar documento del emisor
            if (empty($recibo->emisor_numero_documento)) {
                $errores[] = "Recibo #{$num}: Documento del emisor está vacío";
            }

            // Validar documento del receptor
            if (empty($recibo->cliente->numero_documento)) {
                $errores[] = "Recibo #{$num}: Documento del cliente está vacío";
            }

            // Validar descripción
            if (empty($recibo->descripcion_servicio)) {
                $errores[] = "Recibo #{$num}: Descripción del servicio está vacía";
            }

            // Validar monto
            if ($recibo->monto_bruto <= 0) {
                $errores[] = "Recibo #{$num}: Monto debe ser mayor a 0";
            }

            // Validar fecha
            if (empty($recibo->fecha_emision)) {
                $errores[] = "Recibo #{$num}: Fecha de emisión está vacía";
            }
        }

        return $errores;
    }
}
