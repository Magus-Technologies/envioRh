<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HistorialEmision;
use App\Models\Recibo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProcesamientoController extends Controller
{
    public function index(Request $request)
    {
        $estado = $request->query('estado', 'en_cola');

        $query = Recibo::with(['cliente', 'lote.user'])
            ->orderBy('created_at', 'desc');

        if ($estado === 'en_cola') {
            $query->where('estado', 'en_cola');
        } elseif ($estado === 'procesado') {
            $query->where('estado', 'emitido')->whereNotNull('archivo_pdf');
        }

        $recibos = $query->paginate(25);

        $contadores = [
            'en_cola' => Recibo::where('estado', 'en_cola')->count(),
            'procesado' => Recibo::where('estado', 'emitido')->whereNotNull('archivo_pdf')->count(),
        ];

        return view('admin.procesamiento.index', compact('recibos', 'estado', 'contadores'));
    }

    public function procesar(Request $request, int $id)
    {
        $validated = $request->validate([
            'numero_recibo_sunat' => 'required|string|max:50',
            'archivo_pdf' => 'required|file|mimes:pdf|max:5120',
        ]);

        $recibo = Recibo::findOrFail($id);

        if ($recibo->estado !== 'en_cola') {
            return back()->with('error', 'Este recibo no está en cola de procesamiento.');
        }

        $nombre = 'RHE-' . $recibo->id . '-' . time() . '.pdf';
        $ruta = $request->file('archivo_pdf')->storeAs('recibos_pdf', $nombre, 'public');

        $recibo->update([
            'numero_recibo_sunat' => trim($validated['numero_recibo_sunat']),
            'archivo_pdf' => $ruta,
            'estado' => 'emitido',
            'fecha_procesado' => now(),
        ]);

        HistorialEmision::create([
            'lote_id' => $recibo->lote_id,
            'accion' => 'procesado_por_admin',
            'descripcion' => "Admin subió PDF para Recibo #{$id}. N° SUNAT: {$validated['numero_recibo_sunat']}",
        ]);

        return back()->with('success', "Recibo #{$id} marcado como procesado.");
    }

    public function descargarPdf(int $id)
    {
        $recibo = Recibo::findOrFail($id);

        if (auth()->user()->esCliente()) {
            if (! $recibo->lote || $recibo->lote->user_id !== auth()->id()) {
                abort(403);
            }
        }

        if (! $recibo->archivo_pdf || ! Storage::disk('public')->exists($recibo->archivo_pdf)) {
            return back()->with('error', 'PDF no disponible.');
        }

        return Storage::disk('public')->download(
            $recibo->archivo_pdf,
            'RHE-' . ($recibo->numero_recibo_sunat ?: $recibo->id) . '.pdf'
        );
    }
}
