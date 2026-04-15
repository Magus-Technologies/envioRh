@extends('layouts.app')

@section('title', 'Reporte de Retenciones')

@section('content')
@php $meses = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre']; $mesNum = (int) $mes; @endphp
<div class="max-w-[1400px] mx-auto px-5 lg:px-8 py-10">

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-end mb-10 pb-6 border-b-2 border-ink">
        <div class="lg:col-span-8">
            <p class="kicker">Control · {{ $meses[$mesNum] ?? '' }} {{ $anio }}</p>
            <h1 class="font-display text-5xl font-semibold text-ink mt-2 leading-none">Retenciones <span class="italic">mensuales</span></h1>
            <p class="mt-3 text-ink-3">Monto acumulado por emisor y control del tope exonerado de S/ 1,500.</p>
        </div>
        <div class="lg:col-span-4 flex lg:justify-end">
            <a href="{{ route('reportes.exportar', ['tipo' => 'retenciones', 'mes' => $mes, 'anio' => $anio]) }}" class="btn-primary">Exportar Excel</a>
        </div>
    </div>

    <form method="GET" action="{{ route('reportes.retenciones') }}" class="flex gap-4 items-end mb-8 pb-6 border-b rule">
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

    <div class="card-ledger overflow-hidden">
        @if($retenciones->count() > 0)
            <table class="table-ledger">
                <thead>
                    <tr>
                        <th>Emisor</th>
                        <th>Documento</th>
                        <th class="text-right">Monto acumulado</th>
                        <th class="text-right">Retención acum.</th>
                        <th>Tope excedido</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($retenciones as $r)
                    <tr>
                        <td class="text-ink">{{ $r->emisor_nombre ?? $r->nombre ?? '—' }}</td>
                        <td class="font-mono-num text-ink-3">{{ $r->emisor_numero_documento ?? $r->documento ?? '—' }}</td>
                        <td class="text-right font-mono-num text-ink">S/ {{ number_format($r->monto_acumulado ?? 0, 2) }}</td>
                        <td class="text-right font-mono-num text-clay-ink">S/ {{ number_format($r->retencion_acumulada ?? 0, 2) }}</td>
                        <td>
                            @if(($r->monto_acumulado ?? 0) > 1500)
                                <span class="badge badge-clay">Sí · supera</span>
                            @else
                                <span class="badge badge-forest">No</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="py-16 text-center font-display italic text-2xl text-ink-3">Sin retenciones registradas.</p>
        @endif
    </div>
</div>
@endsection
