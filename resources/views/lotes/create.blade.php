@extends('layouts.app')

@section('title', 'Crear Lote')

@section('content')
@php
    $meses = [1=>'Enero', 2=>'Febrero', 3=>'Marzo', 4=>'Abril', 5=>'Mayo', 6=>'Junio', 7=>'Julio', 8=>'Agosto', 9=>'Septiembre', 10=>'Octubre', 11=>'Noviembre', 12=>'Diciembre'];
@endphp
<div class="max-w-[1400px] mx-auto px-5 lg:px-8 py-10">

    <nav class="text-[11px] uppercase tracking-[0.14em] mb-6 flex items-center gap-2 text-ink-4">
        <a href="{{ route('lotes.index') }}" class="hover:text-amber-ink">Lotes</a>
        <span>/</span>
        <span class="text-ink">Nuevo</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">

        <div class="lg:col-span-5 fade-up fade-up-1">
            <p class="kicker">Paso 1 de 4</p>
            <h1 class="font-display text-5xl font-semibold text-ink mt-2 leading-none">Nuevo <span class="italic">lote</span>.</h1>
            <p class="mt-5 text-ink-3 leading-relaxed">Un lote agrupa los recibos por honorarios de un mismo período mensual. El código se genera automáticamente en formato <code class="font-mono-num text-xs bg-paper-2 border rule px-1.5 py-0.5">LOTE-YYYYMM-###</code>.</p>

            <div class="mt-10 pt-6 border-t rule space-y-4">
                <div class="flex gap-4">
                    <div class="font-mono-num text-xs text-ink-4 pt-0.5">01</div>
                    <div>
                        <div class="text-sm text-ink font-semibold">Elige el período</div>
                        <div class="text-xs text-ink-3">Mes y año que representa el lote.</div>
                    </div>
                </div>
                <div class="flex gap-4 opacity-60">
                    <div class="font-mono-num text-xs text-ink-4 pt-0.5">02</div>
                    <div>
                        <div class="text-sm text-ink-2">Agrega recibos</div>
                        <div class="text-xs text-ink-3">Importa o crea manualmente.</div>
                    </div>
                </div>
                <div class="flex gap-4 opacity-40">
                    <div class="font-mono-num text-xs text-ink-4 pt-0.5">03</div>
                    <div>
                        <div class="text-sm text-ink-2">Genera archivo SUNAT</div>
                    </div>
                </div>
                <div class="flex gap-4 opacity-40">
                    <div class="font-mono-num text-xs text-ink-4 pt-0.5">04</div>
                    <div>
                        <div class="text-sm text-ink-2">Descarga y sube</div>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('lotes.store') }}" class="lg:col-span-7 fade-up fade-up-2">
            @csrf
            <div class="card-ledger">
                <div class="p-6 border-b rule-soft">
                    <h2 class="font-display text-xl text-ink">Datos del lote</h2>
                </div>
                <div class="p-6 space-y-6">

                    <div class="grid grid-cols-2 gap-5">
                        <div>
                            <label for="periodo_mes" class="label-ledger">Mes</label>
                            <select name="periodo_mes" id="periodo_mes" required class="input-ledger">
                                @foreach($meses as $num => $nombre)
                                    <option value="{{ $num }}" {{ old('periodo_mes', date('n')) == $num ? 'selected' : '' }}>{{ $nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="periodo_anio" class="label-ledger">Año</label>
                            <select name="periodo_anio" id="periodo_anio" required class="input-ledger font-mono-num">
                                @for($y = date('Y') + 1; $y >= 2024; $y--)
                                    <option value="{{ $y }}" {{ old('periodo_anio', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="descripcion" class="label-ledger">Descripción <span class="text-ink-4 normal-case tracking-normal font-normal">(opcional)</span></label>
                        <input type="text" name="descripcion" id="descripcion" value="{{ old('descripcion') }}" placeholder="Ej: Recibos de consultoría {{ $meses[date('n')] }} {{ date('Y') }}" class="input-ledger">
                    </div>
                </div>
                <div class="bg-paper px-6 py-4 border-t rule-soft flex items-center justify-between">
                    <a href="{{ route('lotes.index') }}" class="btn-secondary">Cancelar</a>
                    <button type="submit" class="btn-primary">
                        Crear lote
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
