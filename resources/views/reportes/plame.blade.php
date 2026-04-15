@extends('layouts.app')

@section('title', 'Reporte PLAME')

@section('content')
@php
    $totalBruto = collect($porEmisor)->sum('total_bruto');
    $totalRet = collect($porEmisor)->sum('total_retencion');
    $totalNeto = collect($porEmisor)->sum('total_neto');
    $totalCant = collect($porEmisor)->sum('cantidad_recibos');
    $meses = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
    $mesNum = (int) $mes;
@endphp
<div class="max-w-[1400px] mx-auto px-5 lg:px-8 py-10">

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-end mb-10 pb-6 border-b-2 border-ink">
        <div class="lg:col-span-8">
            <p class="kicker">Reporte · {{ $meses[$mesNum] ?? '' }} {{ $anio }}</p>
            <h1 class="font-display text-5xl font-semibold text-ink mt-2 leading-none">PLAME <span class="italic">mensual</span></h1>
            <p class="mt-3 text-ink-3">Agrupación por emisor para declaración mensual a SUNAT.</p>
        </div>
        <div class="lg:col-span-4 flex lg:justify-end">
            <a href="{{ route('reportes.exportar', ['tipo' => 'plame', 'mes' => $mes, 'anio' => $anio]) }}" class="btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                Exportar Excel
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('reportes.plame') }}" class="flex gap-4 items-end mb-8 pb-6 border-b rule">
        <div>
            <label class="label-ledger">Mes</label>
            <select name="mes" class="input-ledger" style="min-width: 160px;">
                @foreach($meses as $n => $m)<option value="{{ $n }}" {{ $mes == $n ? 'selected' : '' }}>{{ $m }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="label-ledger">Año</label>
            <select name="anio" class="input-ledger font-mono-num">
                @for($y=date('Y');$y>=2024;$y--)<option value="{{ $y }}" {{ $anio == $y ? 'selected' : '' }}>{{ $y }}</option>@endfor
            </select>
        </div>
        <button type="submit" class="btn-primary">Aplicar</button>
    </form>

    <div class="grid grid-cols-2 lg:grid-cols-4 border rule bg-surface mb-10">
        <div class="p-6 border-r rule-soft">
            <div class="kicker !text-ink-3">Recibos</div>
            <div class="font-display text-4xl font-light text-ink mt-2 tabular">{{ $totalCant }}</div>
        </div>
        <div class="p-6 border-r rule-soft">
            <div class="kicker !text-ink-3">Bruto</div>
            <div class="font-mono-num text-2xl font-light text-ink mt-2 tabular">S/ {{ number_format($totalBruto, 2) }}</div>
        </div>
        <div class="p-6 border-r rule-soft">
            <div class="kicker !text-ink-3">Retención</div>
            <div class="font-mono-num text-2xl font-light text-clay-ink mt-2 tabular">S/ {{ number_format($totalRet, 2) }}</div>
        </div>
        <div class="p-6 bg-ink text-paper">
            <div class="kicker" style="color: var(--gold);">Neto · S/</div>
            <div class="font-mono-num text-2xl font-light mt-2 tabular">{{ number_format($totalNeto, 2) }}</div>
        </div>
    </div>

    <h2 class="font-display text-2xl font-semibold text-ink mb-4 pb-3 border-b-2 border-ink">Agrupación por emisor</h2>

    <div class="card-ledger overflow-hidden">
        @if(count($porEmisor) > 0)
            <table class="table-ledger">
                <thead>
                    <tr>
                        <th>Documento</th>
                        <th>Nombre</th>
                        <th class="text-right">Recibos</th>
                        <th class="text-right">Bruto</th>
                        <th class="text-right">Retención</th>
                        <th class="text-right">Neto</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($porEmisor as $row)
                        @php $r = (object)$row; @endphp
                        <tr>
                            <td class="font-mono-num text-ink">{{ $r->documento }}</td>
                            <td class="text-ink">{{ $r->nombre }}</td>
                            <td class="text-right font-mono-num tabular">{{ $r->cantidad_recibos }}</td>
                            <td class="text-right font-mono-num tabular text-ink">S/ {{ number_format($r->total_bruto, 2) }}</td>
                            <td class="text-right font-mono-num tabular text-clay-ink">S/ {{ number_format($r->total_retencion, 2) }}</td>
                            <td class="text-right font-mono-num tabular text-forest-ink font-semibold">S/ {{ number_format($r->total_neto, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-paper-2 font-semibold border-t-2 border-ink">
                        <td colspan="2" class="font-display">Totales</td>
                        <td class="text-right font-mono-num">{{ $totalCant }}</td>
                        <td class="text-right font-mono-num">S/ {{ number_format($totalBruto, 2) }}</td>
                        <td class="text-right font-mono-num text-clay-ink">S/ {{ number_format($totalRet, 2) }}</td>
                        <td class="text-right font-mono-num text-forest-ink">S/ {{ number_format($totalNeto, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        @else
            <p class="py-16 text-center font-display italic text-2xl text-ink-3">Sin recibos emitidos en este período.</p>
        @endif
    </div>
</div>
@endsection
