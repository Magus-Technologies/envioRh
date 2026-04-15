@extends('layouts.app')

@section('title', $cliente->nombre_razon_social)

@section('content')
<div class="max-w-[1400px] mx-auto px-5 lg:px-8 py-10">

    <nav class="text-[11px] uppercase tracking-[0.14em] mb-6 flex items-center gap-2 text-ink-4">
        <a href="{{ route('clientes.index') }}" class="hover:text-amber-ink">Clientes</a>
        <span>/</span>
        <span class="text-ink normal-case tracking-normal">{{ Str::limit($cliente->nombre_razon_social, 30) }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-end mb-10 pb-6 border-b-2 border-ink">
        <div class="lg:col-span-8">
            <div class="flex items-center gap-3">
                <span class="badge {{ $cliente->estado === 'activo' ? 'badge-forest' : 'badge-ink' }}">{{ $cliente->estado }}</span>
                <span class="font-mono-num text-xs text-ink-4">{{ $cliente->tipo_documento }} · {{ $cliente->numero_documento }}</span>
            </div>
            <h1 class="font-display text-5xl font-semibold text-ink mt-3 leading-[1.05]">{{ $cliente->nombre_razon_social }}</h1>
            @if($cliente->actividad_economica)
                <p class="mt-3 text-ink-3">{{ $cliente->actividad_economica }}</p>
            @endif
        </div>
        <div class="lg:col-span-4 flex lg:justify-end gap-2">
            <a href="{{ route('clientes.edit', $cliente->id) }}" class="btn-primary">Editar</a>
            <a href="{{ route('clientes.index') }}" class="btn-secondary">Volver</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 mb-10">
        <div class="lg:col-span-8">
            <h2 class="font-display text-2xl font-semibold text-ink mb-4 pb-3 border-b-2 border-ink">Información</h2>
            <dl class="divide-rule">
                <div class="grid grid-cols-3 gap-4 py-3">
                    <dt class="label-ledger !mb-0">Email</dt>
                    <dd class="col-span-2 text-ink font-mono-num">{{ $cliente->email ?: '—' }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-4 py-3">
                    <dt class="label-ledger !mb-0">Teléfono</dt>
                    <dd class="col-span-2 text-ink font-mono-num">{{ $cliente->telefono ?: '—' }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-4 py-3">
                    <dt class="label-ledger !mb-0">Dirección</dt>
                    <dd class="col-span-2 text-ink">{{ $cliente->direccion ?: '—' }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-4 py-3">
                    <dt class="label-ledger !mb-0">Actividad</dt>
                    <dd class="col-span-2 text-ink">{{ $cliente->actividad_economica ?: '—' }}</dd>
                </div>
                <div class="grid grid-cols-3 gap-4 py-3">
                    <dt class="label-ledger !mb-0">Registrado</dt>
                    <dd class="col-span-2 text-ink font-mono-num">{{ $cliente->created_at->format('d/m/Y H:i') }}</dd>
                </div>
            </dl>
        </div>
        <div class="lg:col-span-4">
            <div class="bg-ink text-paper p-6 -mx-5 lg:mx-0">
                <div class="kicker" style="color: var(--gold);">Resumen histórico</div>
                <div class="font-display text-6xl font-light mt-3 tabular">{{ str_pad($cliente->recibos->count(), 2, '0', STR_PAD_LEFT) }}</div>
                <div class="text-[11px] uppercase tracking-wider text-[color:var(--ink-4)]">Recibos emitidos</div>
                <div class="mt-5 pt-5 border-t" style="border-color: rgba(255,255,255,0.1);">
                    <div class="font-mono-num text-2xl font-light tabular">S/ {{ number_format($cliente->recibos->sum('monto_bruto'), 2) }}</div>
                    <div class="text-[11px] uppercase tracking-wider text-[color:var(--ink-4)] mt-0.5">Monto total</div>
                </div>
            </div>
        </div>
    </div>

    <h2 class="font-display text-2xl font-semibold text-ink mb-4 pb-3 border-b-2 border-ink">Recibos emitidos</h2>
    <div class="card-ledger overflow-hidden">
        @if($cliente->recibos->count() > 0)
            <table class="table-ledger">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Descripción</th>
                        <th class="text-right">Bruto</th>
                        <th class="text-right">Retención</th>
                        <th class="text-right">Neto</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cliente->recibos->sortByDesc('fecha_emision') as $recibo)
                    <tr>
                        <td class="font-mono-num text-ink-3">{{ \Carbon\Carbon::parse($recibo->fecha_emision)->format('d/m/Y') }}</td>
                        <td class="text-ink">{{ Str::limit($recibo->descripcion_servicio, 50) }}</td>
                        <td class="text-right font-mono-num">{{ number_format($recibo->monto_bruto, 2) }}</td>
                        <td class="text-right font-mono-num text-clay-ink">{{ number_format($recibo->monto_retencion, 2) }}</td>
                        <td class="text-right font-mono-num font-semibold text-forest-ink">{{ number_format($recibo->monto_neto, 2) }}</td>
                        <td><span class="badge {{ $recibo->estado === 'emitido' ? 'badge-forest' : 'badge-gold' }}">{{ $recibo->estado }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="py-12 text-center font-display italic text-xl text-ink-3">Este cliente aún no tiene recibos.</p>
        @endif
    </div>
</div>
@endsection
