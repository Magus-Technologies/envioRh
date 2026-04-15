@extends('layouts.app')

@section('title', 'Resumen de Lotes')

@section('content')
<div class="max-w-[1400px] mx-auto px-5 lg:px-8 py-10">

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-end mb-10 pb-6 border-b-2 border-ink">
        <div class="lg:col-span-8">
            <p class="kicker">Vista consolidada</p>
            <h1 class="font-display text-5xl font-semibold text-ink mt-2 leading-none">Resumen de <span class="italic">lotes</span></h1>
            <p class="mt-3 text-ink-3">Vista consolidada de todos los lotes procesados por el sistema.</p>
        </div>
        <div class="lg:col-span-4 flex lg:justify-end">
            <a href="{{ route('reportes.exportar', ['tipo' => 'lotes']) }}" class="btn-primary">Exportar Excel</a>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-5 border rule bg-surface mb-10">
        <div class="p-6 border-r rule-soft">
            <div class="kicker !text-ink-3">Lotes</div>
            <div class="font-display text-4xl font-light text-ink mt-2 tabular">{{ $estadisticas['total_lotes'] ?? 0 }}</div>
        </div>
        <div class="p-6 border-r rule-soft">
            <div class="kicker !text-ink-3">Recibos</div>
            <div class="font-display text-4xl font-light text-ink mt-2 tabular">{{ $estadisticas['total_recibos'] ?? 0 }}</div>
        </div>
        <div class="p-6 border-r rule-soft">
            <div class="kicker !text-ink-3">Bruto</div>
            <div class="font-mono-num text-xl font-light text-ink mt-2 tabular">S/ {{ number_format($estadisticas['monto_total'] ?? 0, 2) }}</div>
        </div>
        <div class="p-6 border-r rule-soft">
            <div class="kicker !text-ink-3">Retención</div>
            <div class="font-mono-num text-xl font-light text-clay-ink mt-2 tabular">S/ {{ number_format($estadisticas['retencion_total'] ?? 0, 2) }}</div>
        </div>
        <div class="p-6 bg-ink text-paper">
            <div class="kicker" style="color: var(--gold);">Neto · S/</div>
            <div class="font-mono-num text-xl font-light mt-2 tabular">{{ number_format($estadisticas['neto_total'] ?? 0, 2) }}</div>
        </div>
    </div>

    <form method="GET" action="{{ route('reportes.resumen-lotes') }}" class="flex gap-4 items-end mb-8 pb-6 border-b rule">
        <div>
            <label class="label-ledger">Año</label>
            <select name="anio" class="input-ledger font-mono-num" style="min-width: 140px;">
                <option value="">Todos</option>
                @for($y=date('Y');$y>=2024;$y--)<option value="{{ $y }}" {{ request('anio') == $y ? 'selected' : '' }}>{{ $y }}</option>@endfor
            </select>
        </div>
        <button type="submit" class="btn-primary">Filtrar</button>
    </form>

    <div class="card-ledger overflow-hidden">
        @if($lotes->count() > 0)
            <table class="table-ledger">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Período</th>
                        <th class="text-right">Recibos</th>
                        <th class="text-right">Bruto</th>
                        <th class="text-right">Retención</th>
                        <th class="text-right">Neto</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lotes as $lote)
                    <tr>
                        <td><a href="{{ route('lotes.show', $lote->id) }}" class="font-mono-num text-ink hover:text-amber-ink">{{ $lote->codigo_lote }}</a></td>
                        <td class="font-mono-num text-ink-3">{{ str_pad($lote->periodo_mes,2,'0',STR_PAD_LEFT) }}/{{ $lote->periodo_anio }}</td>
                        <td class="text-right font-mono-num tabular">{{ $lote->total_recibos }}</td>
                        <td class="text-right font-mono-num tabular font-semibold">S/ {{ number_format($lote->monto_total, 2) }}</td>
                        <td class="text-right font-mono-num tabular text-clay-ink">S/ {{ number_format($lote->retencion_total, 2) }}</td>
                        <td class="text-right font-mono-num tabular text-forest-ink font-semibold">S/ {{ number_format($lote->neto_total ?? ($lote->monto_total - $lote->retencion_total), 2) }}</td>
                        <td>
                            @php $b = ['emitido' => 'badge-forest', 'generado' => 'badge-amber', 'pendiente' => 'badge-gold'][$lote->estado] ?? 'badge-ink'; @endphp
                            <span class="badge {{ $b }}">{{ $lote->estado }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="py-16 text-center font-display italic text-2xl text-ink-3">No hay lotes en el período seleccionado.</p>
        @endif
    </div>
</div>
@endsection
