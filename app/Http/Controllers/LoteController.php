<?php

namespace App\Http\Controllers;

use App\Models\LoteEmision;
use App\Models\Recibo;
use App\Models\HistorialEmision;
use App\Services\SunatFileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoteController extends Controller
{
    protected SunatFileService $sunatFileService;

    public function __construct(SunatFileService $sunatFileService)
    {
        $this->sunatFileService = $sunatFileService;
    }

    /**
     * Listar lotes
     */
    public function index(Request $request)
    {
        $query = LoteEmision::query();

        if (auth()->user() && auth()->user()->esCliente()) {
            $query->where('user_id', auth()->id());
        }

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('mes') && $request->has('anio')) {
            $query->where('periodo_mes', $request->mes)
                  ->where('periodo_anio', $request->anio);
        }

        $lotes = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('lotes.index', compact('lotes'));
    }

    public function enviarASunat(int $id)
    {
        $lote = LoteEmision::findOrFail($id);

        if (auth()->user()->esCliente() && $lote->user_id !== auth()->id()) {
            abort(403);
        }

        $count = Recibo::where('lote_id', $id)
            ->whereIn('estado', ['pendiente', 'validado', 'generado'])
            ->update(['estado' => 'en_cola']);

        if ($count === 0) {
            return back()->with('error', 'No hay recibos pendientes para enviar.');
        }

        HistorialEmision::create([
            'lote_id' => $id,
            'accion' => 'enviado_a_procesamiento',
            'descripcion' => "{$count} recibo(s) enviado(s) a procesamiento SUNAT",
        ]);

        $lote->update(['estado' => 'subido_sunat', 'fecha_subida' => now()]);

        return back()->with('success', "Se enviaron {$count} recibo(s) a SUNAT. Serán procesados en breve.");
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        return view('lotes.create');
    }

    /**
     * Almacenar nuevo lote
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'descripcion' => 'nullable|string|max:255',
            'periodo_mes' => 'required|integer|min:1|max:12',
            'periodo_anio' => 'required|integer|min:2020|max:2099',
        ]);

        // Generar código de lote
        $codigo = $this->generarCodigoLote($validated['periodo_mes'], $validated['periodo_anio']);

        $lote = LoteEmision::create([
            'codigo_lote' => $codigo,
            'periodo_mes' => $validated['periodo_mes'],
            'periodo_anio' => $validated['periodo_anio'],
            'descripcion' => $validated['descripcion'],
            'estado' => 'pendiente',
            'user_id' => auth()->id(),
            'creado_por' => auth()->id(),
        ]);

        // Registrar en historial
        HistorialEmision::create([
            'lote_id' => $lote->id,
            'accion' => 'lote_creado',
            'descripcion' => "Lote {$codigo} creado",
        ]);

        return redirect()->route('lotes.show', $lote->id)
                         ->with('success', 'Lote creado exitosamente');
    }

    /**
     * Mostrar detalle del lote
     */
    public function show(int $id)
    {
        $lote = LoteEmision::with(['recibos.cliente', 'historial'])->findOrFail($id);

        if (auth()->user() && auth()->user()->esCliente() && $lote->user_id && $lote->user_id !== auth()->id()) {
            abort(403);
        }

        $recibos = $lote->recibos()->paginate(20);

        return view('lotes.show', compact('lote', 'recibos'));
    }

    /**
     * Generar archivo para SUNAT
     */
    public function generarArchivo(int $id, Request $request)
    {
        $lote = LoteEmision::with('recibos.cliente')->findOrFail($id);

        // Validar que tenga recibos
        if ($lote->recibos()->count() === 0) {
            return back()->with('error', 'El lote no tiene recibos para generar el archivo');
        }

        try {
            $ruta = $this->sunatFileService->generarArchivoExcel($lote);

            HistorialEmision::create([
                'lote_id' => $id,
                'accion' => 'archivo_generado',
                'descripcion' => "Excel masivo generado: {$ruta}",
            ]);

            return redirect()->route('lotes.descargar', $id);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al generar Excel: ' . $e->getMessage());
        }
    }

    /**
     * Descargar archivo generado
     */
    public function descargarArchivo(int $id)
    {
        $lote = LoteEmision::findOrFail($id);

        if (empty($lote->archivo_ruta)) {
            return back()->with('error', 'No hay archivo generado para este lote');
        }

        try {
            $archivo = $this->sunatFileService->descargarArchivo($lote->archivo_ruta);

            // Registrar en historial
            HistorialEmision::create([
                'lote_id' => $id,
                'accion' => 'archivo_descargado',
                'descripcion' => "Archivo descargado: {$archivo['nombre']}",
            ]);

            return response($archivo['contenido'])
                ->header('Content-Type', $archivo['tipo'])
                ->header('Content-Disposition', 'attachment; filename="' . $archivo['nombre'] . '"');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al descargar archivo: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar lote
     */
    public function destroy(int $id)
    {
        $lote = LoteEmision::findOrFail($id);

        // Verificar que no tenga recibos emitidos
        if ($lote->recibos()->where('estado', 'emitido')->count() > 0) {
            return back()->with('error', 'No se puede eliminar un lote con recibos emitidos');
        }

        $lote->delete();

        return redirect()->route('lotes.index')
                         ->with('success', 'Lote eliminado exitosamente');
    }

    /**
     * Generar código de lote
     */
    protected function generarCodigoLote(int $mes, int $anio): string
    {
        $mesStr = str_pad($mes, 2, '0', STR_PAD_LEFT);
        
        $ultimo = LoteEmision::where('periodo_mes', $mes)
                             ->where('periodo_anio', $anio)
                             ->orderBy('id', 'desc')
                             ->first();

        $consecutivo = $ultimo ? (intval(substr($ultimo->codigo_lote, -3)) + 1) : 1;

        return sprintf('LOTE-%d%s-%03d', $anio, $mesStr, $consecutivo);
    }
}
