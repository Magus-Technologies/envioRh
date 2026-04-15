<?php

namespace App\Services;

use App\Models\Recibo;
use App\Models\Cliente;
use App\Models\LoteEmision;
use App\Models\ArchivoImportado;
use App\Models\HistorialEmision;
use App\Services\RetencionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ImportacionService
{
    protected RetencionService $retencionService;

    public function __construct(RetencionService $retencionService)
    {
        $this->retencionService = $retencionService;
    }

    /**
     * Importar archivo Excel/CSV
     */
    public function importarArchivo(UploadedFile $archivo, int $loteId, string $importadoPor): array
    {
        $extension = $archivo->getClientOriginalExtension();
        $tipoArchivo = in_array($extension, ['xlsx', 'xls']) ? 'excel' : 'csv';

        // Registrar archivo importado
        $archivoImportado = ArchivoImportado::create([
            'lote_id' => $loteId,
            'nombre_archivo' => $archivo->hashName(),
            'nombre_original' => $archivo->getClientOriginalName(),
            'tipo_archivo' => $tipoArchivo,
            'tamanio_bytes' => $archivo->getSize(),
            'importado_por' => $importadoPor,
        ]);

        // Guardar archivo
        $ruta = $archivo->store('importaciones', 'local');

        try {
            // Importar datos
            $resultado = $this->procesarArchivo($ruta, $loteId, $tipoArchivo);

            // Actualizar registro
            $archivoImportado->update([
                'total_registros' => $resultado['total'],
                'registros_validos' => $resultado['validos'],
                'registros_invalidos' => $resultado['invalidos'],
                'errores' => !empty($resultado['errores']) ? json_encode($resultado['errores']) : null,
                'estado' => $resultado['invalidos'] > 0 ? 'parcial' : 'importado',
            ]);

            // Registrar en historial
            HistorialEmision::create([
                'lote_id' => $loteId,
                'accion' => 'datos_importados',
                'descripcion' => "Se importaron {$resultado['validos']} registros de {$resultado['total']} total",
                'usuario' => $importadoPor,
                'datos_adicionales' => $resultado,
            ]);

            return $resultado;
        } catch (\Exception $e) {
            $archivoImportado->update([
                'estado' => 'error',
                'errores' => json_encode(['error' => $e->getMessage()]),
            ]);

            throw $e;
        }
    }

    /**
     * Procesar archivo importado
     */
    protected function procesarArchivo(string $ruta, int $loteId, string $tipoArchivo): array
    {
        $datos = [];

        if ($tipoArchivo === 'excel') {
            $datos = Excel::toArray([], storage_path('app/' . $ruta));
            $datos = $datos[0]; // Primera hoja
        } else {
            // Procesar CSV
            $contenido = file_get_contents(storage_path('app/' . $ruta));
            $lineas = explode("\n", $contenido);
            
            // Asumir que la primera línea son encabezados
            $encabezados = str_getcsv(array_shift($lineas));
            
            foreach ($lineas as $linea) {
                if (empty(trim($linea))) continue;
                $datos[] = str_getcsv($linea);
            }
        }

        // Eliminar fila de encabezados si existe
        if (!empty($datos) && $this->esEncabezado($datos[0])) {
            array_shift($datos);
        }

        return $this->procesarDatos($datos, $loteId);
    }

    /**
     * Verificar si es fila de encabezados
     */
    protected function esEncabezado(array $fila): bool
    {
        $primerValor = strtolower((string) $fila[0]);
        return in_array($primerValor, ['tipo', 'documento', 'cliente', 'descripcion', 'monto']);
    }

    /**
     * Procesar datos e insertar en BD
     */
    protected function procesarDatos(array $datos, int $loteId): array
    {
        $resultado = [
            'total' => count($datos),
            'validos' => 0,
            'invalidos' => 0,
            'errores' => [],
        ];

        $lote = LoteEmision::findOrFail($loteId);

        DB::beginTransaction();

        try {
            foreach ($datos as $index => $fila) {
                $num = $index + 1;

                try {
                    // Parsear fila
                    $parseado = $this->parsearFila($fila, $num);

                    if ($parseado === null) {
                        continue;
                    }

                    // Validar datos
                    $errores = $this->validarFila($parseado, $num);

                    if (!empty($errores)) {
                        $resultado['errores'] = array_merge($resultado['errores'], $errores);
                        $resultado['invalidos']++;
                        continue;
                    }

                    // Buscar o crear cliente
                    $cliente = Cliente::firstOrCreate(
                        [
                            'tipo_documento' => $parseado['tipo_documento_cliente'],
                            'numero_documento' => $parseado['documento_cliente'],
                        ],
                        [
                            'nombre_razon_social' => $parseado['nombre_cliente'] ?? '',
                        ]
                    );

                    // Calcular retención
                    $fechaEmision = Carbon::parse($parseado['fecha_emision']);
                    $retencion = $this->retencionService->calcularRetencion(
                        $parseado['documento_emisor'],
                        $fechaEmision,
                        $parseado['monto']
                    );

                    // Crear recibo
                    Recibo::create([
                        'lote_id' => $loteId,
                        'cliente_id' => $cliente->id,
                        'emisor_tipo_documento' => $parseado['tipo_documento_emisor'] ?? 'DNI',
                        'emisor_numero_documento' => $parseado['documento_emisor'],
                        'emisor_nombre' => $parseado['nombre_emisor'] ?? '',
                        'descripcion_servicio' => $parseado['descripcion'],
                        'fecha_emision' => $fechaEmision,
                        'fecha_vencimiento' => $parseado['fecha_vencimiento'] ? Carbon::parse($parseado['fecha_vencimiento']) : null,
                        'monto_bruto' => $parseado['monto'],
                        'aplica_retencion' => $retencion['aplica_retencion'],
                        'porcentaje_retencion' => $this->retencionService->getPorcentajeRetencion(),
                        'monto_retencion' => $retencion['monto_retencion'],
                        'monto_neto' => $retencion['monto_neto'],
                        'moneda' => $parseado['moneda'] ?? 'PEN',
                        'numero_continuacion' => $parseado['numero_continuacion'] ?? null,
                        'estado' => 'pendiente',
                    ]);

                    // Actualizar acumulado
                    if ($retencion['aplica_retencion']) {
                        $this->retencionService->actualizarAcumulado(
                            $parseado['documento_emisor'],
                            $fechaEmision,
                            $parseado['monto'],
                            $retencion['monto_retencion']
                        );
                    }

                    $resultado['validos']++;
                } catch (\Exception $e) {
                    $resultado['errores'][] = "Fila #{$num}: " . $e->getMessage();
                    $resultado['invalidos']++;
                }
            }

            DB::commit();

            return $resultado;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Parsear una fila de datos
     */
    protected function parsearFila(array $fila, int $num): ?array
    {
        // Asumir formato estándar:
        // [0] Tipo Doc Emisor, [1] Doc Emisor, [2] Nombre Emisor, 
        // [3] Tipo Doc Cliente, [4] Doc Cliente, [5] Nombre Cliente,
        // [6] Descripción, [7] Monto, [8] Fecha Emisión, [9] Fecha Vencimiento,
        // [10] Moneda, [11] Número Continuación

        if (count($fila) < 7) {
            return null; // Fila incompleta
        }

        return [
            'tipo_documento_emisor' => $this->limpiarTexto($fila[0] ?? 'DNI'),
            'documento_emisor' => $this->limpiarTexto($fila[1] ?? ''),
            'nombre_emisor' => $this->limpiarTexto($fila[2] ?? ''),
            'tipo_documento_cliente' => $this->limpiarTexto($fila[3] ?? 'RUC'),
            'documento_cliente' => $this->limpiarTexto($fila[4] ?? ''),
            'nombre_cliente' => $this->limpiarTexto($fila[5] ?? ''),
            'descripcion' => $this->limpiarTexto($fila[6] ?? ''),
            'monto' => $this->parsearMonto($fila[7] ?? '0'),
            'fecha_emision' => $this->parsearFecha($fila[8] ?? date('Y-m-d')),
            'fecha_vencimiento' => isset($fila[9]) ? $this->parsearFecha($fila[9]) : null,
            'moneda' => $this->limpiarTexto($fila[10] ?? 'PEN'),
            'numero_continuacion' => $this->limpiarTexto($fila[11] ?? null),
        ];
    }

    /**
     * Validar una fila de datos
     */
    protected function validarFila(array $datos, int $num): array
    {
        $errores = [];

        if (empty($datos['documento_emisor'])) {
            $errores[] = "Fila #{$num}: Documento del emisor está vacío";
        }

        if (empty($datos['documento_cliente'])) {
            $errores[] = "Fila #{$num}: Documento del cliente está vacío";
        }

        if (empty($datos['descripcion'])) {
            $errores[] = "Fila #{$num}: Descripción del servicio está vacía";
        }

        if ($datos['monto'] <= 0) {
            $errores[] = "Fila #{$num}: Monto debe ser mayor a 0";
        }

        return $errores;
    }

    /**
     * Limpiar texto
     */
    protected function limpiarTexto(?string $texto): ?string
    {
        if ($texto === null) return null;
        return trim($texto);
    }

    /**
     * Parsear monto
     */
    protected function parsearMonto(string $monto): float
    {
        // Eliminar símbolos de moneda y comas
        $monto = str_replace(['S/', '$', ',', ' '], '', $monto);
        return (float) $monto;
    }

    /**
     * Parsear fecha
     */
    protected function parsearFecha(string $fecha): string
    {
        // Intentar varios formatos
        $formatos = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y', 'Y/m/d'];
        
        foreach ($formatos as $formato) {
            $date = Carbon::createFromFormat($formato, $fecha);
            if ($date) {
                return $date->format('Y-m-d');
            }
        }

        // Si no se pudo parsear, usar fecha actual
        return date('Y-m-d');
    }
}
