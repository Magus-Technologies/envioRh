@extends('layouts.app')

@section('title', 'Lote ' . $lote->codigo_lote)

@section('content')
<div class="max-w-[1400px] mx-auto px-5 lg:px-8 py-10">

    <nav class="text-[11px] uppercase tracking-[0.14em] mb-6 flex items-center gap-2 text-ink-4">
        <a href="{{ route('lotes.index') }}" class="hover:text-amber-ink">Lotes</a>
        <span>/</span>
        <span class="text-ink font-mono-num normal-case tracking-normal">{{ $lote->codigo_lote }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-end mb-10 pb-6 border-b-2 border-ink">
        <div class="lg:col-span-7">
            @php $b = ['emitido' => 'badge-forest', 'generado' => 'badge-amber', 'pendiente' => 'badge-gold'][$lote->estado] ?? 'badge-ink'; @endphp
            <div class="flex items-center gap-3">
                <span class="badge {{ $b }}">{{ $lote->estado }}</span>
                <span class="font-mono-num text-xs text-ink-4">{{ str_pad($lote->periodo_mes, 2, '0', STR_PAD_LEFT) }}/{{ $lote->periodo_anio }}</span>
            </div>
            <h1 class="font-display text-5xl font-semibold text-ink mt-3 leading-none font-mono-num">{{ $lote->codigo_lote }}</h1>
            <p class="mt-3 text-ink-3">{{ $lote->descripcion ?: 'Sin descripción' }}</p>
        </div>
        <div class="lg:col-span-5 flex lg:justify-end flex-wrap gap-2">
            @if($lote->estado === 'pendiente')
                <a href="{{ route('importacion.create', $lote->id) }}" class="btn-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Importar
                </a>
            @endif
            @if($lote->recibos->count() > 0 && in_array($lote->estado, ['pendiente', 'generado']))
                <form method="POST" action="{{ route('lotes.generar-archivo', $lote->id) }}">
                    @csrf
                    <button type="submit" class="btn-amber">Generar archivo SUNAT</button>
                </form>
            @endif
            @if($lote->archivo_generado)
                <a href="{{ route('lotes.descargar', $lote->id) }}" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                    Descargar
                </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 border rule bg-surface mb-10">
        <div class="p-6 border-r rule-soft">
            <div class="kicker !text-ink-3">Recibos</div>
            <div class="font-display text-4xl font-light text-ink mt-2 tabular">{{ $lote->total_recibos }}</div>
        </div>
        <div class="p-6 border-r rule-soft">
            <div class="kicker !text-ink-3">Bruto</div>
            <div class="font-mono-num text-2xl font-light text-ink mt-2 tabular">S/ {{ number_format($lote->monto_total, 2) }}</div>
        </div>
        <div class="p-6 border-r rule-soft">
            <div class="kicker !text-ink-3">Retención 8%</div>
            <div class="font-mono-num text-2xl font-light text-clay-ink mt-2 tabular">S/ {{ number_format($lote->retencion_total, 2) }}</div>
        </div>
        <div class="p-6 bg-ink text-paper">
            <div class="kicker" style="color: var(--gold);">Neto · S/</div>
            <div class="font-mono-num text-2xl font-light mt-2 tabular">{{ number_format($lote->neto_total, 2) }}</div>
        </div>
    </div>

    <div class="flex items-baseline justify-between mb-4 pb-3 border-b-2 border-ink">
        <h2 class="font-display text-3xl font-semibold text-ink">Recibos del lote</h2>
        <span class="text-xs text-ink-4 font-mono-num">{{ $recibos->total() ?? $recibos->count() }} registros</span>
    </div>

    <div class="card-ledger overflow-hidden mb-10">
        @if($recibos->count() > 0)
            <div class="overflow-x-auto">
                <table class="table-ledger">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Emisor</th>
                            <th>Cliente</th>
                            <th>Descripción</th>
                            <th>Fecha</th>
                            <th class="text-right">Bruto</th>
                            <th class="text-right">Retención</th>
                            <th class="text-right">Neto</th>
                            <th>Estado</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recibos as $recibo)
                        <tr>
                            <td class="font-mono-num text-ink-4">{{ str_pad($recibo->id, 3, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                <div class="text-ink">{{ Str::limit($recibo->emisor_nombre, 24) }}</div>
                                <div class="text-[11px] text-ink-4 font-mono-num">{{ $recibo->emisor_tipo_documento }} {{ $recibo->emisor_numero_documento }}</div>
                            </td>
                            <td>
                                <div class="text-ink">{{ Str::limit($recibo->cliente->nombre_razon_social, 24) }}</div>
                                <div class="text-[11px] text-ink-4 font-mono-num">{{ $recibo->cliente->tipo_documento }} {{ $recibo->cliente->numero_documento }}</div>
                            </td>
                            <td class="text-ink-3">{{ Str::limit($recibo->descripcion_servicio, 30) }}</td>
                            <td class="font-mono-num text-ink-3">{{ \Carbon\Carbon::parse($recibo->fecha_emision)->format('d/m/y') }}</td>
                            <td class="text-right font-mono-num text-ink">{{ number_format($recibo->monto_bruto, 2) }}</td>
                            <td class="text-right font-mono-num text-clay-ink">{{ number_format($recibo->monto_retencion, 2) }}</td>
                            <td class="text-right font-mono-num font-semibold text-forest-ink">{{ number_format($recibo->monto_neto, 2) }}</td>
                            <td>
                                <span class="badge {{ $recibo->estado === 'emitido' ? 'badge-forest' : 'badge-gold' }}">{{ $recibo->estado }}</span>
                            </td>
                            <td class="text-right text-xs space-x-3">
                                @if($recibo->estado !== 'emitido')
                                    <a href="{{ route('recibos.edit', $recibo->id) }}" class="text-ink hover:text-amber-ink uppercase tracking-wider">Editar</a>
                                    <form method="POST" action="{{ route('recibos.destroy', $recibo->id) }}" class="inline" onsubmit="return confirm('¿Eliminar recibo?')">
                                        @csrf @method('DELETE')
                                        <button class="text-clay-ink hover:text-ink uppercase tracking-wider">Borrar</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(method_exists($recibos, 'links') && $recibos->hasPages())
                <div class="p-4 border-t rule-soft bg-paper">{{ $recibos->links() }}</div>
            @endif
        @else
            <div class="py-16 text-center">
                <p class="font-display italic text-2xl text-ink-3">Aún no hay recibos.</p>
                <a href="{{ route('importacion.create', $lote->id) }}" class="btn-primary mt-5">Importar recibos</a>
            </div>
        @endif
    </div>

    @if($lote->historial->count() > 0)
        <div class="mb-10">
            <h2 class="font-display text-2xl font-semibold text-ink mb-4 pb-3 border-b-2 border-ink">Bitácora</h2>
            <div class="divide-rule">
                @foreach($lote->historial->take(10) as $historial)
                    <div class="flex items-start gap-4 py-3">
                        <div class="font-mono-num text-xs text-ink-4 w-20 pt-0.5">{{ \Carbon\Carbon::parse($historial->fecha_accion)->format('d/m H:i') }}</div>
                        <div class="w-1.5 h-1.5 rounded-full bg-amber-ink mt-2 flex-shrink-0"></div>
                        <div class="flex-1 text-sm text-ink-2">{{ $historial->descripcion }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
