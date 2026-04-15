@extends('layouts.app')

@section('title', 'Historial de Importaciones')

@section('content')
<div class="max-w-[1400px] mx-auto px-5 lg:px-8 py-10">

    <nav class="text-[11px] uppercase tracking-[0.14em] mb-6 flex items-center gap-2 text-ink-4">
        <a href="{{ route('lotes.index') }}" class="hover:text-amber-ink">Lotes</a>
        <span>/</span>
        <a href="{{ route('lotes.show', $lote->id) }}" class="hover:text-amber-ink font-mono-num normal-case tracking-normal">{{ $lote->codigo_lote }}</a>
        <span>/</span>
        <span class="text-ink">Historial</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-end mb-10 pb-6 border-b-2 border-ink">
        <div class="lg:col-span-8">
            <p class="kicker">Bitácora de importaciones</p>
            <h1 class="font-display text-5xl font-semibold text-ink mt-2 leading-none">Historial</h1>
        </div>
        <div class="lg:col-span-4 flex lg:justify-end">
            <a href="{{ route('importacion.create', $lote->id) }}" class="btn-primary">Nueva importación</a>
        </div>
    </div>

    <div class="card-ledger overflow-hidden">
        @if($archivos->count() > 0)
            <table class="table-ledger">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Archivo</th>
                        <th class="text-right">Exitosos</th>
                        <th class="text-right">Fallidos</th>
                        <th>Estado</th>
                        <th>Usuario</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($archivos as $archivo)
                    <tr>
                        <td class="font-mono-num text-ink-3">{{ \Carbon\Carbon::parse($archivo->fecha_importacion ?? $archivo->created_at)->format('d/m/Y H:i') }}</td>
                        <td class="text-ink font-mono-num">{{ $archivo->nombre_archivo ?? $archivo->archivo ?? '—' }}</td>
                        <td class="text-right font-mono-num text-forest-ink">{{ $archivo->registros_exitosos ?? $archivo->total_registros ?? 0 }}</td>
                        <td class="text-right font-mono-num text-clay-ink">{{ $archivo->registros_fallidos ?? 0 }}</td>
                        <td>
                            @php $estado = $archivo->estado ?? 'procesado'; @endphp
                            <span class="badge {{ in_array($estado, ['procesado','exitoso']) ? 'badge-forest' : ($estado === 'fallido' ? 'badge-clay' : 'badge-gold') }}">{{ $estado }}</span>
                        </td>
                        <td class="text-ink-3 text-xs">{{ $archivo->usuario ?? $archivo->creado_por ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="py-16 text-center">
                <p class="font-display italic text-2xl text-ink-3">Sin importaciones para este lote.</p>
                <a href="{{ route('importacion.create', $lote->id) }}" class="btn-primary mt-5">Importar ahora</a>
            </div>
        @endif
    </div>
</div>
@endsection
