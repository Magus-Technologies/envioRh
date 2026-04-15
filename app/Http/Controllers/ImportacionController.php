<?php

namespace App\Http\Controllers;

use App\Models\LoteEmision;
use App\Models\ArchivoImportado;
use App\Services\ImportacionService;
use Illuminate\Http\Request;

class ImportacionController extends Controller
{
    protected ImportacionService $importacionService;

    public function __construct(ImportacionService $importacionService)
    {
        $this->importacionService = $importacionService;
    }

    /**
     * Mostrar formulario de importación
     */
    public function create(int $loteId)
    {
        $lote = LoteEmision::findOrFail($loteId);

        return view('importacion.create', compact('lote'));
    }

    /**
     * Procesar archivo importado
     */
    public function store(Request $request, int $loteId)
    {
        $validated = $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240', // 10MB max
        ]);

        $lote = LoteEmision::findOrFail($loteId);

        try {
            $resultado = $this->importacionService->importarArchivo(
                $validated['archivo'],
                $loteId,
                'usuario' // TODO: Obtener usuario autenticado
            );

            $mensaje = "Se importaron {$resultado['validos']} registros de {$resultado['total']} total";
            
            if ($resultado['invalidos'] > 0) {
                $mensaje .= ". {$resultado['invalidos']} tuvieron errores.";
            }

            return redirect()->route('lotes.show', $loteId)
                             ->with('success', $mensaje);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Importacion QueryException', ['error' => $e->getMessage(), 'lote' => $loteId]);
            return back()->with('error', 'No se pudo guardar la importación en la base de datos. Revisa que el archivo tenga las columnas correctas y vuelve a intentarlo.')->withInput();
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            return back()->with('error', 'El archivo no se pudo leer. Verifica que sea un Excel válido (.xlsx, .xls) o CSV.')->withInput();
        } catch (\Exception $e) {
            \Log::error('Importacion Exception', ['error' => $e->getMessage(), 'lote' => $loteId]);
            return back()->with('error', 'Ocurrió un error al importar el archivo. Intenta nuevamente o descarga la plantilla oficial.')->withInput();
        }
    }

    /**
     * Descargar plantilla Excel
     */
    public function descargarPlantilla()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Encabezados
        $sheet->fromArray([
            [
                'Tipo Doc Emisor (DNI/CE/Pasaporte)',
                'Documento Emisor',
                'Nombre Emisor',
                'Tipo Doc Cliente (DNI/RUC)',
                'Documento Cliente',
                'Nombre Cliente',
                'Descripción del Servicio',
                'Monto',
                'Fecha Emisión (YYYY-MM-DD)',
                'Fecha Vencimiento (YYYY-MM-DD)',
                'Moneda (PEN/USD)',
                'Número Continuación (opcional)'
            ]
        ], null, 'A1');

        // Ejemplo
        $sheet->fromArray([
            [
                'DNI',
                '12345678',
                'Juan Pérez López',
                'RUC',
                '20123456789',
                'Empresa Ejemplo SAC',
                'Servicio de consultoría en sistemas',
                1500.00,
                '2026-04-01',
                '2026-04-30',
                'PEN',
                'CONT-2026-001'
            ]
        ], null, 'A2');

        // Estilo encabezados
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempFile = storage_path('app/plantilla_rhe.xlsx');
        $writer->save($tempFile);

        return response()->download($tempFile, 'plantilla_rhe.xlsx')->deleteFileAfterSend(true);
    }

    /**
     * Historial de importaciones
     */
    public function historial(int $loteId)
    {
        $lote = LoteEmision::findOrFail($loteId);
        $archivos = ArchivoImportado::where('lote_id', $loteId)
                                     ->orderBy('fecha_importacion', 'desc')
                                     ->get();

        return view('importacion.historial', compact('lote', 'archivos'));
    }
}
