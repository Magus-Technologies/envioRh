@extends('layouts.app')

@section('title', 'Lotes')

@section('content')
<div class="max-w-[1400px] mx-auto px-5 lg:px-8 py-10">

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-end mb-10 pb-6 border-b-2 border-ink">
        <div class="lg:col-span-8">
            <p class="kicker">Módulo · Gestión mensual</p>
            <h1 class="font-display text-5xl font-semibold text-ink mt-2 leading-none">Lotes de <span class="italic">emisión</span></h1>
            <p class="mt-3 text-ink-3">Cada lote agrupa los recibos de un período mensual que envías a SUNAT.</p>
        </div>
        <div class="lg:col-span-4 flex lg:justify-end">
            <a href="{{ route('lotes.create') }}" class="btn-primary">
                <span>+</span> Nuevo lote
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('lotes.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8 p-5 bg-surface border rule">
        <div>
            <label class="label-ledger">Estado</label>
            <select name="estado" class="input-ledger">
                <option value="">Todos</option>
                <option value="pendiente" {{ request('estado') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                <option value="generado" {{ request('estado') === 'generado' ? 'selected' : '' }}>Generado</option>
                <option value="emitido" {{ request('estado') === 'emitido' ? 'selected' : '' }}>Emitido</option>
            </select>
        </div>
        <div>
            <label class="label-ledger">Mes</label>
            <select name="mes" class="input-ledger">
                <option value="">Todos</option>
                @foreach([1=>'Ene',2=>'Feb',3=>'Mar',4=>'Abr',5=>'May',6=>'Jun',7=>'Jul',8=>'Ago',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dic'] as $n=>$m)
                    <option value="{{ $n }}" {{ request('mes') == $n ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label-ledger">Año</label>
            <select name="anio" class="input-ledger">
                <option value="">Todos</option>
                @for($y = date('Y'); $y >= 2024; $y--)<option value="{{ $y }}" {{ request('anio') == $y ? 'selected' : '' }}>{{ $y }}</option>@endfor
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="btn-primary flex-1 justify-center">Filtrar</button>
            <a href="{{ route('lotes.index') }}" class="btn-secondary">Limpiar</a>
        </div>
    </form>

    <div class="card-ledger overflow-hidden">
        @if($lotes->count() > 0)
            <table class="table-ledger">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Período</th>
                        <th>Descripción</th>
                        <th class="text-right">Recibos</th>
                        <th class="text-right">Monto bruto</th>
                        <th class="text-right">Retención</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lotes as $lote)
                    <tr>
                        <td>
                            <a href="{{ route('lotes.show', $lote->id) }}" class="font-mono-num text-ink hover:text-amber-ink">{{ $lote->codigo_lote }}</a>
                        </td>
                        <td class="font-mono-num text-ink-3">{{ str_pad($lote->periodo_mes, 2, '0', STR_PAD_LEFT) }}/{{ $lote->periodo_anio }}</td>
                        <td class="text-ink-3">{{ Str::limit($lote->descripcion, 35) ?: '—' }}</td>
                        <td class="text-right font-mono-num tabular">{{ $lote->total_recibos }}</td>
                        <td class="text-right font-mono-num tabular font-semibold text-ink">S/ {{ number_format($lote->monto_total, 2) }}</td>
                        <td class="text-right font-mono-num tabular text-clay-ink">S/ {{ number_format($lote->retencion_total, 2) }}</td>
                        <td>
                            @php $b = ['emitido' => 'badge-forest', 'generado' => 'badge-amber', 'pendiente' => 'badge-gold'][$lote->estado] ?? 'badge-ink'; @endphp
                            <span class="badge {{ $b }}">{{ $lote->estado }}</span>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('lotes.show', $lote->id) }}" class="text-ink hover:text-amber-ink text-xs uppercase tracking-wider">Abrir →</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @if($lotes->hasPages())
                <div class="p-4 border-t rule-soft bg-paper">{{ $lotes->links() }}</div>
            @endif
        @else
            <div class="py-20 text-center">
                <p class="font-display italic text-3xl text-ink-3">No hay lotes creados.</p>
                <p class="text-sm text-ink-4 mt-2">Crea el primero para comenzar a emitir recibos.</p>
                <a href="{{ route('lotes.create') }}" class="btn-primary mt-6">Crear primer lote</a>
            </div>
        @endif
    </div>
</div>
@endsection
