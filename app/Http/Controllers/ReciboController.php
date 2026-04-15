<?php

namespace App\Http\Controllers;

use App\Models\Recibo;
use App\Models\LoteEmision;
use App\Models\Cliente;
use App\Models\HistorialEmision;
use App\Services\RetencionService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReciboController extends Controller
{
    protected RetencionService $retencionService;

    public function __construct(RetencionService $retencionService)
    {
        $this->retencionService = $retencionService;
    }

    /**
     * Almacenar nuevo recibo en un lote
     */
    public function store(Request $request, int $loteId)
    {
        $lote = LoteEmision::findOrFail($loteId);

        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'emisor_tipo_documento' => 'required|in:DNI,CE,Pasaporte',
            'emisor_numero_documento' => 'required|string|max:20',
            'emisor_nombre' => 'required|string|max:255',
            'descripcion_servicio' => 'required|string|max:500',
            'fecha_emision' => 'required|date',
            'fecha_vencimiento' => 'nullable|date',
            'monto_bruto' => 'required|numeric|min:0.01',
            'moneda' => 'required|in:PEN,USD',
            'numero_continuacion' => 'nullable|string|max:50',
        ]);

        // Calcular retención
        $fechaEmision = Carbon::parse($validated['fecha_emision']);
        $retencion = $this->retencionService->calcularRetencion(
            $validated['emisor_numero_documento'],
            $fechaEmision,
            $validated['monto_bruto']
        );

        // Crear recibo
        $recibo = Recibo::create([
            'lote_id' => $loteId,
            'cliente_id' => $validated['cliente_id'],
            'emisor_tipo_documento' => $validated['emisor_tipo_documento'],
            'emisor_numero_documento' => $validated['emisor_numero_documento'],
            'emisor_nombre' => $validated['emisor_nombre'],
            'descripcion_servicio' => $validated['descripcion_servicio'],
            'fecha_emision' => $fechaEmision,
            'fecha_vencimiento' => $validated['fecha_vencimiento'] ? Carbon::parse($validated['fecha_vencimiento']) : null,
            'monto_bruto' => $validated['monto_bruto'],
            'aplica_retencion' => $retencion['aplica_retencion'],
            'porcentaje_retencion' => $this->retencionService->getPorcentajeRetencion(),
            'monto_retencion' => $retencion['monto_retencion'],
            'monto_neto' => $retencion['monto_neto'],
            'moneda' => $validated['moneda'],
            'numero_continuacion' => $validated['numero_continuacion'],
            'estado' => 'pendiente',
        ]);

        // Actualizar acumulado si aplica retención
        if ($retencion['aplica_retencion']) {
            $this->retencionService->actualizarAcumulado(
                $validated['emisor_numero_documento'],
                $fechaEmision,
                $validated['monto_bruto'],
                $retencion['monto_retencion']
            );
        }

        // Registrar en historial
        HistorialEmision::create([
            'lote_id' => $loteId,
            'accion' => 'recibo_agregado',
            'descripcion' => "Recibo #{$recibo->id} agregado al lote",
        ]);

        return back()->with('success', 'Recibo agregado exitosamente');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(int $id)
    {
        $recibo = Recibo::with('cliente')->findOrFail($id);
        $clientes = Cliente::where('estado', 'activo')->get();

        return view('recibos.edit', compact('recibo', 'clientes'));
    }

    /**
     * Actualizar recibo
     */
    public function update(Request $request, int $id)
    {
        $recibo = Recibo::findOrFail($id);

        // Verificar que no esté emitido
        if ($recibo->estado === 'emitido') {
            return back()->with('error', 'No se puede editar un recibo ya emitido');
        }

        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'emisor_tipo_documento' => 'required|in:DNI,CE,Pasaporte',
            'emisor_numero_documento' => 'required|string|max:20',
            'emisor_nombre' => 'required|string|max:255',
            'descripcion_servicio' => 'required|string|max:500',
            'fecha_emision' => 'required|date',
            'fecha_vencimiento' => 'nullable|date',
            'monto_bruto' => 'required|numeric|min:0.01',
            'moneda' => 'required|in:PEN,USD',
            'numero_continuacion' => 'nullable|string|max:50',
        ]);

        // Recalcular retención
        $fechaEmision = Carbon::parse($validated['fecha_emision']);
        $retencion = $this->retencionService->calcularRetencion(
            $validated['emisor_numero_documento'],
            $fechaEmision,
            $validated['monto_bruto']
        );

        $recibo->update([
            'cliente_id' => $validated['cliente_id'],
            'emisor_tipo_documento' => $validated['emisor_tipo_documento'],
            'emisor_numero_documento' => $validated['emisor_numero_documento'],
            'emisor_nombre' => $validated['emisor_nombre'],
            'descripcion_servicio' => $validated['descripcion_servicio'],
            'fecha_emision' => $fechaEmision,
            'fecha_vencimiento' => $validated['fecha_vencimiento'] ? Carbon::parse($validated['fecha_vencimiento']) : null,
            'monto_bruto' => $validated['monto_bruto'],
            'aplica_retencion' => $retencion['aplica_retencion'],
            'monto_retencion' => $retencion['monto_retencion'],
            'monto_neto' => $retencion['monto_neto'],
            'moneda' => $validated['moneda'],
            'numero_continuacion' => $validated['numero_continuacion'],
        ]);

        // Registrar en historial
        HistorialEmision::create([
            'lote_id' => $recibo->lote_id,
            'accion' => 'recibo_editado',
            'descripcion' => "Recibo #{$recibo->id} editado",
        ]);

        return redirect()->route('lotes.show', $recibo->lote_id)
                         ->with('success', 'Recibo actualizado exitosamente');
    }

    /**
     * Eliminar recibo
     */
    public function destroy(int $id)
    {
        $recibo = Recibo::findOrFail($id);

        // Verificar que no esté emitido
        if ($recibo->estado === 'emitido') {
            return back()->with('error', 'No se puede eliminar un recibo ya emitido');
        }

        $loteId = $recibo->lote_id;
        $recibo->delete();

        // Registrar en historial
        HistorialEmision::create([
            'lote_id' => $loteId,
            'accion' => 'recibo_editado',
            'descripcion' => "Recibo #{$id} eliminado",
        ]);

        return back()->with('success', 'Recibo eliminado exitosamente');
    }

    /**
     * Calcular retención (API)
     */
    public function calcularRetencion(Request $request)
    {
        $validated = $request->validate([
            'emisor_documento' => 'required|string',
            'fecha_emision' => 'required|date',
            'monto_bruto' => 'required|numeric|min:0',
        ]);

        $fechaEmision = Carbon::parse($validated['fecha_emision']);
        $retencion = $this->retencionService->calcularRetencion(
            $validated['emisor_documento'],
            $fechaEmision,
            $validated['monto_bruto']
        );

        return response()->json($retencion);
    }
}
