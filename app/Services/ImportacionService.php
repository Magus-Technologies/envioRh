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

        $rutaAbsoluta = \Storage::disk('local')->path($ruta);

        if ($tipoArchivo === 'excel') {
            $datos = Excel::toArray([], $rutaAbsoluta);
            $datos = $datos[0]; // Primera hoja
        } else {
            // Procesar CSV
            $contenido = file_get_contents($rutaAbsoluta);
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
        $primerValor = strtolower(trim((string) ($fila[0] ?? '')));

        if ($primerValor === '') {
            return false;
        }

        // Palabras clave que solo aparecen en encabezados
        $keywords = ['tipo', 'documento', 'cliente', 'descripcion', 'descripción', 'monto', 'fecha', 'moneda', 'emisor', 'nombre'];

        foreach ($keywords as $k) {
            if (str_contains($primerValor, $k)) {
                return true;
            }
        }

        // Si la celda del monto (índice 7) no es numérica, probablemente es encabezado
        if (isset($fila[7]) && !is_numeric(trim((string) $fila[7]))) {
            $montoCol = strtolower(trim((string) $fila[7]));
            if (str_contains($montoCol, 'monto') || str_contains($montoCol, 'total')) {
                return true;
            }
        }

        return false;
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
                    $fechaEmision = Carbon::createFromFormat('Y-m-d', $parseado['fecha_emision'])->startOfDay();
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
                        'fecha_vencimiento' => $parseado['fecha_vencimiento'] ? Carbon::createFromFormat('Y-m-d', $parseado['fecha_vencimiento'])->startOfDay() : null,
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
                } catch (\Illuminate\Database\QueryException $e) {
                    $resultado['errores'][] = "Fila #{$num}: " . $this->traducirErrorSql($e);
                    $resultado['invalidos']++;
                } catch (\Exception $e) {
                    $resultado['errores'][] = "Fila #{$num}: No se pudo guardar el recibo. Revisa los datos de esta fila.";
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
            'fecha_emision' => $this->parsearFecha($fila[8] ?? null),
            'fecha_vencimiento' => $this->parsearFecha($fila[9] ?? null),
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

        if (empty($datos['fecha_emision'])) {
            $errores[] = "Fila #{$num}: Fecha de emisión vacía o con formato inválido (usa YYYY-MM-DD, por ejemplo 2026-04-15)";
        }

        $tiposEmisorValidos = ['DNI', 'CE', 'Pasaporte'];
        if (!empty($datos['tipo_documento_emisor']) && !in_array($datos['tipo_documento_emisor'], $tiposEmisorValidos, true)) {
            $errores[] = "Fila #{$num}: Tipo de documento del emisor inválido (\"{$datos['tipo_documento_emisor']}\"). El emisor es una persona natural: usa DNI, CE o Pasaporte";
        }

        $tiposClienteValidos = ['DNI', 'RUC', 'CE', 'Pasaporte'];
        if (!empty($datos['tipo_documento_cliente']) && !in_array($datos['tipo_documento_cliente'], $tiposClienteValidos, true)) {
            $errores[] = "Fila #{$num}: Tipo de documento del cliente inválido (\"{$datos['tipo_documento_cliente']}\"). Usa DNI, RUC, CE o Pasaporte";
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
     * Parsear fecha. Devuelve 'Y-m-d' o null si no se pudo interpretar.
     */
    protected function parsearFecha($fecha): ?string
    {
        if ($fecha === null || $fecha === '') {
            return null;
        }

        // Fecha numérica de Excel (serial date)
        if (is_numeric($fecha)) {
            try {
                $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $fecha);
                return $dt->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        $fecha = trim((string) $fecha);

        $formatos = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y', 'Y/m/d', 'd/m/y'];

        foreach ($formatos as $formato) {
            $date = \DateTime::createFromFormat($formato, $fecha);
            $errors = \DateTime::getLastErrors();
            if ($date && (empty($errors['warning_count']) && empty($errors['error_count']))) {
                return $date->format('Y-m-d');
            }
        }

        // Último intento: Carbon::parse genérico
        try {
            return Carbon::parse($fecha)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Traduce un error SQL a mensaje claro en español
     */
    protected function traducirErrorSql(\Illuminate\Database\QueryException $e): string
    {
        $msg = $e->getMessage();

        if (str_contains($msg, 'Data truncated for column')) {
            preg_match("/column '([^']+)'/", $msg, $m);
            $campo = $m[1] ?? 'un campo';
            return "El valor del campo \"{$campo}\" no es válido. Verifica que uses solo los valores permitidos (ej: DNI/CE/Pasaporte para emisor, DNI/RUC para cliente, PEN/USD para moneda).";
        }

        if (str_contains($msg, 'Data too long for column')) {
            preg_match("/column '([^']+)'/", $msg, $m);
            $campo = $m[1] ?? 'un campo';
            return "El texto del campo \"{$campo}\" es demasiado largo. Acórtalo e intenta de nuevo.";
        }

        if (str_contains($msg, 'cannot be null') || str_contains($msg, "doesn't have a default value")) {
            preg_match("/[Cc]olumn '([^']+)'/", $msg, $m);
            $campo = $m[1] ?? 'un campo obligatorio';
            return "Falta completar el campo \"{$campo}\".";
        }

        if (str_contains($msg, 'Duplicate entry')) {
            return "Este recibo ya existe en la base de datos (registro duplicado).";
        }

        if (str_contains($msg, 'Incorrect date value') || str_contains($msg, 'Incorrect datetime value')) {
            return "Una de las fechas de esta fila no tiene un formato válido. Usa YYYY-MM-DD (ej: 2026-04-15).";
        }

        if (str_contains($msg, 'Out of range value')) {
            preg_match("/column '([^']+)'/", $msg, $m);
            $campo = $m[1] ?? 'un campo numérico';
            return "El valor del campo \"{$campo}\" está fuera del rango permitido.";
        }

        return "No se pudo guardar el recibo. Revisa los datos de esta fila.";
    }
}
