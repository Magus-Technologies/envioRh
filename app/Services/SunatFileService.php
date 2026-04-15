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
     * Generar archivo Excel (alternativa)
     */
    public function generarArchivoExcel(LoteEmision $lote): string
    {
        $recibos = $lote->recibos()->where('estado', '!=', 'anulado')->get();

        if ($recibos->isEmpty()) {
            throw new \Exception('El lote no tiene recibos para generar el archivo');
        }

        $nombreArchivo = str_replace('.txt', '.xlsx', $this->generarNombreArchivo($lote));
        $ruta = 'exports/sunat/' . $nombreArchivo;

        // Crear array de datos
        $datos = [];
        foreach ($recibos as $recibo) {
            $datos[] = [
                'Tipo Doc Receptor' => $recibo->cliente->tipo_documento,
                'Número Doc Receptor' => $recibo->cliente->numero_documento,
                'Descripción Servicio' => $recibo->descripcion_servicio,
                'Monto' => $recibo->monto_bruto,
                'Fecha Emisión' => $recibo->fecha_emision->format('Y-m-d'),
                'Fecha Vencimiento' => $recibo->fecha_vencimiento ? $recibo->fecha_vencimiento->format('Y-m-d') : '',
            ];
        }

        // Usar PhpSpreadsheet directamente
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Encabezados
        $sheet->fromArray([
            ['Tipo Doc Receptor', 'Número Doc Receptor', 'Descripción Servicio', 'Monto', 'Fecha Emisión', 'Fecha Vencimiento']
        ], null, 'A1');

        // Datos
        $sheet->fromArray($datos, null, 'A2');

        // Guardar
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempFile = storage_path('app/' . $ruta);
        
        // Crear directorio si no existe
        $dir = dirname($tempFile);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $writer->save($tempFile);

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
