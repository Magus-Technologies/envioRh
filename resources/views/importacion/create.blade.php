@extends('layouts.app')

@section('title', 'Importar Recibos')

@section('content')
<div class="max-w-[1400px] mx-auto px-5 lg:px-8 py-10">

    <nav class="text-[11px] uppercase tracking-[0.14em] mb-6 flex items-center gap-2 text-ink-4">
        <a href="{{ route('lotes.index') }}" class="hover:text-amber-ink">Lotes</a>
        <span>/</span>
        <a href="{{ route('lotes.show', $lote->id) }}" class="hover:text-amber-ink font-mono-num normal-case tracking-normal">{{ $lote->codigo_lote }}</a>
        <span>/</span>
        <span class="text-ink">Importar</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">

        <div class="lg:col-span-5 fade-up fade-up-1">
            <p class="kicker">Paso 2 de 4</p>
            <h1 class="font-display text-5xl font-semibold text-ink mt-2 leading-none">Importar <span class="italic">recibos</span>.</h1>
            <p class="mt-5 text-ink-3 leading-relaxed">Sube un archivo Excel o CSV con los datos de tus recibos. Los clientes se crearán automáticamente si no existen.</p>

            <div class="mt-8 p-5 bg-surface border rule">
                <div class="kicker !text-ink-3">Destino</div>
                <div class="mt-2">
                    <div class="font-mono-num text-ink">{{ $lote->codigo_lote }}</div>
                    <div class="text-xs text-ink-3">Período {{ str_pad($lote->periodo_mes, 2, '0', STR_PAD_LEFT) }}/{{ $lote->periodo_anio }}</div>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t rule" x-data="{ loading: false }">
                <p class="text-xs text-ink-3 mb-3">¿No tienes plantilla?</p>
                <a href="{{ route('importacion.plantilla') }}"
                   class="btn-secondary"
                   :class="loading && 'opacity-60 pointer-events-none'"
                   @click="loading = true; setTimeout(() => loading = false, 3500)">
                    <template x-if="!loading">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                    </template>
                    <template x-if="loading">
                        <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2.5" opacity="0.25"/><path stroke-linecap="round" stroke-width="2.5" d="M21 12a9 9 0 0 0-9-9"/></svg>
                    </template>
                    <span x-text="loading ? 'Generando…' : 'Descargar plantilla Excel'">Descargar plantilla Excel</span>
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('importacion.store', $lote->id) }}" enctype="multipart/form-data" class="lg:col-span-7 fade-up fade-up-2">
            @csrf

            <div class="card-ledger">
                <div class="p-6 border-b rule-soft">
                    <h2 class="font-display text-xl text-ink">Archivo</h2>
                </div>
                <div class="p-6">
                    <label class="label-ledger">Selecciona archivo</label>
                    <div class="border-2 border-dashed rule p-10 text-center hover:border-amber-ink transition bg-paper" x-data="{ fileName: '' }">
                        <div class="font-display text-5xl font-light text-ink-4">↓</div>
                        <p class="mt-3 text-sm text-ink-3">Arrastra el archivo aquí o click para seleccionar</p>
                        <p class="text-[11px] uppercase tracking-wider text-ink-4 mt-1">XLSX, XLS, CSV · max 10MB</p>
                        <input type="file" name="archivo" accept=".xlsx,.xls,.csv" required class="mt-4 block w-full text-sm text-ink-3 file:mr-4 file:py-2 file:px-4 file:rounded-none file:border file:border-ink file:bg-ink file:text-paper file:text-xs file:uppercase file:tracking-wider hover:file:bg-amber-ink hover:file:border-amber-ink" @change="fileName = $event.target.files[0]?.name">
                        <p x-show="fileName" x-cloak class="mt-3 text-sm font-mono-num text-forest-ink" x-text="fileName"></p>
                    </div>
                </div>
                <div class="bg-paper px-6 py-4 border-t rule-soft flex items-center justify-between">
                    <a href="{{ route('lotes.show', $lote->id) }}" class="btn-secondary">Cancelar</a>
                    <button type="submit" class="btn-primary">
                        Importar archivo
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </div>

            <p class="mt-4 text-center text-xs text-ink-3">
                <a href="{{ route('importacion.historial', $lote->id) }}" class="text-amber-ink hover:text-ink uppercase tracking-wider">Ver historial de importaciones →</a>
            </p>
        </form>
    </div>
</div>
@endsection
