@extends('layouts.app')

@section('title', 'Panel')

@section('content')
@php
    $totalLotes = \App\Models\LoteEmision::count();
    $totalRecibos = \App\Models\Recibo::count();
    $totalClientes = \App\Models\Cliente::where('estado', 'activo')->count();
    $montoTotal = \App\Models\Recibo::where('estado', 'emitido')->sum('monto_bruto');
    $retencionTotal = \App\Models\Recibo::where('estado', 'emitido')->sum('monto_retencion');
    $mesActual = now()->month;
    $anioActual = now()->year;
    $recibosMes = \App\Models\Recibo::whereYear('fecha_emision', $anioActual)->whereMonth('fecha_emision', $mesActual)->count();
    $lotesRecientes = \App\Models\LoteEmision::latest()->take(6)->get();
    $clientesRecientes = \App\Models\Cliente::latest()->take(4)->get();
@endphp

<div class="max-w-[1400px] mx-auto px-5 lg:px-8 py-10">

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-12">
        <div class="lg:col-span-8 fade-up fade-up-1">
            <p class="kicker">Panel de control · {{ now()->isoFormat('MMMM Y') }}</p>
            @php
                $h = now()->hour;
                $saludo = $h < 12 ? 'Buenos días' : ($h < 19 ? 'Buenas tardes' : 'Buenas noches');
            @endphp
            <h1 class="font-display text-5xl lg:text-6xl font-semibold text-ink mt-3 leading-[1.05]">
                {{ $saludo }},<br>
                <span class="italic text-amber-ink">{{ explode(' ', Auth::user()->name)[0] }}</span>.
            </h1>
            <p class="mt-4 text-ink-3 max-w-xl">Este es el resumen de tu operación hasta hoy {{ now()->isoFormat('D [de] MMMM') }}. Todo lo importante, en una sola página.</p>
        </div>
        <div class="lg:col-span-4 lg:border-l rule lg:pl-8 fade-up fade-up-2">
            <div class="space-y-4">
                <div>
                    <div class="kicker !text-ink-3">Período activo</div>
                    <div class="font-display text-3xl font-semibold text-ink mt-1">{{ now()->isoFormat('MMMM') }} <span class="font-mono-num font-light text-ink-3">/{{ $anioActual }}</span></div>
                </div>
                <div class="grid grid-cols-2 gap-3 pt-3 border-t rule-soft">
                    <div>
                        <div class="text-[10px] uppercase tracking-wider text-ink-4">Recibos mes</div>
                        <div class="font-mono-num text-xl text-ink mt-0.5">{{ $recibosMes }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] uppercase tracking-wider text-ink-4">Día</div>
                        <div class="font-mono-num text-xl text-ink mt-0.5">{{ now()->day }}<span class="text-ink-4">/{{ now()->daysInMonth }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 border rule bg-surface mb-12 fade-up fade-up-2">
        <div class="p-6 border-r rule-soft">
            <div class="kicker !text-ink-3">Lotes</div>
            <div class="font-display text-5xl font-light text-ink mt-3 tabular">{{ str_pad($totalLotes, 2, '0', STR_PAD_LEFT) }}</div>
            <div class="mt-3 pt-3 border-t rule-soft text-xs text-ink-4">Total acumulado</div>
        </div>
        <div class="p-6 border-r rule-soft">
            <div class="kicker !text-ink-3">Recibos</div>
            <div class="font-display text-5xl font-light text-ink mt-3 tabular">{{ str_pad($totalRecibos, 2, '0', STR_PAD_LEFT) }}</div>
            <div class="mt-3 pt-3 border-t rule-soft text-xs text-ink-4">{{ $recibosMes }} este mes</div>
        </div>
        <div class="p-6 border-r rule-soft">
            <div class="kicker !text-ink-3">Clientes</div>
            <div class="font-display text-5xl font-light text-ink mt-3 tabular">{{ str_pad($totalClientes, 2, '0', STR_PAD_LEFT) }}</div>
            <div class="mt-3 pt-3 border-t rule-soft text-xs text-ink-4">Activos en cartera</div>
        </div>
        <div class="p-6 bg-ink text-paper">
            <div class="kicker" style="color: var(--gold);">Emitido · S/</div>
            <div class="font-display text-5xl font-light mt-3 tabular">{{ number_format($montoTotal, 0) }}<span class="text-xl text-[color:var(--ink-4)]">.{{ str_pad(intval(($montoTotal - intval($montoTotal)) * 100), 2, '0', STR_PAD_LEFT) }}</span></div>
            <div class="mt-3 pt-3 border-t text-xs font-mono-num" style="border-color: rgba(255,255,255,0.1); color: var(--ink-4);">Retención: S/ {{ number_format($retencionTotal, 2) }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-12">
        <div class="lg:col-span-7 fade-up fade-up-3">
            <div class="flex items-baseline justify-between mb-4 pb-3 border-b-2 border-ink">
                <h2 class="font-display text-2xl font-semibold text-ink">Lotes recientes</h2>
                <a href="{{ route('lotes.index') }}" class="text-xs uppercase tracking-widest text-amber-ink hover:text-ink">Ver todos →</a>
            </div>

            @if($lotesRecientes->count() > 0)
                <div class="divide-rule">
                    @foreach($lotesRecientes as $lote)
                        <a href="{{ route('lotes.show', $lote->id) }}" class="flex items-center gap-4 py-4 hover:bg-paper-2 transition px-2 -mx-2 group">
                            <div class="w-12 text-center">
                                <div class="font-display text-2xl font-light text-ink">{{ str_pad($lote->periodo_mes, 2, '0', STR_PAD_LEFT) }}</div>
                                <div class="text-[10px] uppercase tracking-wider text-ink-4">{{ $lote->periodo_anio }}</div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-mono-num text-sm text-ink">{{ $lote->codigo_lote }}</div>
                                <div class="text-xs text-ink-3 truncate">{{ $lote->descripcion ?: $lote->total_recibos . ' recibos · Creado ' . $lote->created_at->diffForHumans() }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-mono-num text-sm text-ink">S/ {{ number_format($lote->monto_total, 2) }}</div>
                                @php $b = ['emitido' => 'badge-forest', 'generado' => 'badge-amber', 'pendiente' => 'badge-gold'][$lote->estado] ?? 'badge-ink'; @endphp
                                <span class="badge {{ $b }} mt-1">{{ $lote->estado }}</span>
                            </div>
                            <svg class="w-4 h-4 text-ink-4 group-hover:text-amber-ink group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="py-16 text-center">
                    <p class="font-display italic text-2xl text-ink-3">Sin lotes todavía.</p>
                    <a href="{{ route('lotes.create') }}" class="btn-primary mt-6">Crear primer lote</a>
                </div>
            @endif
        </div>

        <div class="lg:col-span-5 space-y-8 fade-up fade-up-4">
            <div>
                <div class="flex items-baseline justify-between mb-4 pb-3 border-b-2 border-ink">
                    <h2 class="font-display text-2xl font-semibold text-ink">Acciones</h2>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-px bg-[color:var(--rule)] border rule">
                    <a href="{{ route('lotes.create') }}" class="bg-surface p-5 hover:bg-paper-2 transition group">
                        <div class="kicker !text-ink-4 group-hover:!text-amber-ink">01</div>
                        <div class="font-display text-lg text-ink mt-1">Nuevo lote</div>
                        <div class="text-xs text-ink-3 mt-1">Período mensual</div>
                    </a>
                    <a href="{{ route('clientes.create') }}" class="bg-surface p-5 hover:bg-paper-2 transition group">
                        <div class="kicker !text-ink-4 group-hover:!text-amber-ink">02</div>
                        <div class="font-display text-lg text-ink mt-1">Nuevo cliente</div>
                        <div class="text-xs text-ink-3 mt-1">Registrar</div>
                    </a>
                    <a href="{{ route('importacion.plantilla') }}" class="bg-surface p-5 hover:bg-paper-2 transition group">
                        <div class="kicker !text-ink-4 group-hover:!text-amber-ink">03</div>
                        <div class="font-display text-lg text-ink mt-1">Plantilla</div>
                        <div class="text-xs text-ink-3 mt-1">Descargar Excel</div>
                    </a>
                </div>
            </div>

            <div>
                <div class="flex items-baseline justify-between mb-4 pb-3 border-b-2 border-ink">
                    <h2 class="font-display text-2xl font-semibold text-ink">Últimos clientes</h2>
                    <a href="{{ route('clientes.index') }}" class="text-xs uppercase tracking-widest text-amber-ink hover:text-ink">Ver todos →</a>
                </div>
                @if($clientesRecientes->count() > 0)
                    <div class="divide-rule">
                        @foreach($clientesRecientes as $cliente)
                            <a href="{{ route('clientes.show', $cliente->id) }}" class="flex items-center gap-3 py-3 hover:bg-paper-2 transition px-2 -mx-2">
                                <div class="w-9 h-9 rounded-full bg-paper-2 border rule flex items-center justify-center font-display text-sm text-ink flex-shrink-0">{{ strtoupper(substr($cliente->nombre_razon_social, 0, 1)) }}</div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm text-ink truncate">{{ $cliente->nombre_razon_social }}</div>
                                    <div class="text-xs text-ink-4 font-mono-num">{{ $cliente->tipo_documento }} {{ $cliente->numero_documento }}</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-ink-3 italic py-4">Sin clientes registrados.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="border-t-2 border-ink pt-8 fade-up fade-up-4">
        <p class="kicker">Cómo funciona</p>
        <h2 class="font-display text-3xl font-semibold text-ink mt-2">Cuatro pasos, un archivo SUNAT.</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-0 mt-8">
            @foreach([
                ['01', 'Crea un lote', 'Agrupa los recibos por mes y año.'],
                ['02', 'Importa datos', 'Desde Excel/CSV o agrega manualmente.'],
                ['03', 'Genera archivo', 'En formato TXT o Excel para SUNAT.'],
                ['04', 'Sube a SUNAT', 'Usa tu clave SOL en el portal.'],
            ] as $i => $step)
                <div class="p-6 border-l rule {{ $loop->last ? 'border-r' : '' }} md:{{ $loop->first ? 'border-l' : '' }}">
                    <div class="font-mono-num text-4xl font-light text-ink-4">{{ $step[0] }}</div>
                    <div class="font-display text-xl text-ink mt-3">{{ $step[1] }}</div>
                    <div class="text-sm text-ink-3 mt-2">{{ $step[2] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
