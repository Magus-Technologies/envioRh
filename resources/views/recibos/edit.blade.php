@extends('layouts.app')

@section('title', 'Editar Recibo')

@section('content')
<div class="max-w-[1400px] mx-auto px-5 lg:px-8 py-10">

    <nav class="text-[11px] uppercase tracking-[0.14em] mb-6 flex items-center gap-2 text-ink-4">
        <a href="{{ route('lotes.show', $recibo->lote_id) }}" class="hover:text-amber-ink">Lote</a>
        <span>/</span>
        <span class="text-ink">Editar recibo</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">

        <div class="lg:col-span-4 fade-up fade-up-1">
            <p class="kicker">Recibo #{{ str_pad($recibo->id, 3, '0', STR_PAD_LEFT) }}</p>
            <h1 class="font-display text-5xl font-semibold text-ink mt-2 leading-none">Editar <span class="italic">recibo</span>.</h1>

            <div class="mt-8 space-y-6">
                <div class="p-5 border rule bg-surface">
                    <div class="kicker !text-ink-3">Estado actual</div>
                    <div class="mt-2">
                        <span class="badge {{ $recibo->estado === 'emitido' ? 'badge-forest' : 'badge-gold' }}">{{ $recibo->estado }}</span>
                    </div>
                </div>

                <div class="p-5 bg-ink text-paper">
                    <div class="kicker" style="color: var(--gold);">Resumen actual</div>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between border-b pb-2" style="border-color: rgba(255,255,255,0.1);">
                            <dt class="text-[color:var(--ink-4)]">Bruto</dt>
                            <dd class="font-mono-num">S/ {{ number_format($recibo->monto_bruto, 2) }}</dd>
                        </div>
                        <div class="flex justify-between border-b pb-2" style="border-color: rgba(255,255,255,0.1);">
                            <dt class="text-[color:var(--ink-4)]">Retención</dt>
                            <dd class="font-mono-num text-[color:var(--amber)]">-S/ {{ number_format($recibo->monto_retencion, 2) }}</dd>
                        </div>
                        <div class="flex justify-between font-semibold">
                            <dt>Neto</dt>
                            <dd class="font-mono-num text-lg">S/ {{ number_format($recibo->monto_neto, 2) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('recibos.update', $recibo->id) }}" class="lg:col-span-8 fade-up fade-up-2">
            @csrf
            @method('PUT')

            <div class="card-ledger mb-6">
                <div class="p-6 border-b rule-soft">
                    <h2 class="font-display text-xl text-ink">Emisor</h2>
                    <p class="text-xs text-ink-3 mt-1">Quién emite el recibo (profesional independiente).</p>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="label-ledger">Tipo documento</label>
                        <select name="emisor_tipo_documento" class="input-ledger">
                            @foreach(['DNI','CE','Pasaporte'] as $td)
                                <option value="{{ $td }}" {{ old('emisor_tipo_documento', $recibo->emisor_tipo_documento) === $td ? 'selected' : '' }}>{{ $td }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label-ledger">Número</label>
                        <input type="text" name="emisor_numero_documento" value="{{ old('emisor_numero_documento', $recibo->emisor_numero_documento) }}" required class="input-ledger font-mono-num">
                    </div>
                    <div>
                        <label class="label-ledger">Nombre</label>
                        <input type="text" name="emisor_nombre" value="{{ old('emisor_nombre', $recibo->emisor_nombre) }}" required class="input-ledger">
                    </div>
                </div>
            </div>

            <div class="card-ledger mb-6">
                <div class="p-6 border-b rule-soft">
                    <h2 class="font-display text-xl text-ink">Cliente</h2>
                </div>
                <div class="p-6">
                    <label class="label-ledger">Seleccionar cliente</label>
                    <select name="cliente_id" required class="input-ledger">
                        <option value="">— Seleccionar —</option>
                        @foreach($clientes as $c)
                            <option value="{{ $c->id }}" {{ old('cliente_id', $recibo->cliente_id) == $c->id ? 'selected' : '' }}>{{ $c->tipo_documento }} {{ $c->numero_documento }} — {{ $c->nombre_razon_social }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="card-ledger">
                <div class="p-6 border-b rule-soft">
                    <h2 class="font-display text-xl text-ink">Datos del recibo</h2>
                </div>
                <div class="p-6 space-y-5">
                    <div>
                        <label class="label-ledger">Descripción del servicio</label>
                        <input type="text" name="descripcion_servicio" value="{{ old('descripcion_servicio', $recibo->descripcion_servicio) }}" required maxlength="500" class="input-ledger">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <label class="label-ledger">Fecha emisión</label>
                            <input type="date" name="fecha_emision" value="{{ old('fecha_emision', \Carbon\Carbon::parse($recibo->fecha_emision)->format('Y-m-d')) }}" required class="input-ledger font-mono-num">
                        </div>
                        <div>
                            <label class="label-ledger">Fecha vencimiento</label>
                            <input type="date" name="fecha_vencimiento" value="{{ old('fecha_vencimiento', $recibo->fecha_vencimiento ? \Carbon\Carbon::parse($recibo->fecha_vencimiento)->format('Y-m-d') : '') }}" class="input-ledger font-mono-num">
                        </div>
                        <div>
                            <label class="label-ledger">Moneda</label>
                            <select name="moneda" class="input-ledger">
                                <option value="PEN" {{ old('moneda', $recibo->moneda) === 'PEN' ? 'selected' : '' }}>PEN · Soles</option>
                                <option value="USD" {{ old('moneda', $recibo->moneda) === 'USD' ? 'selected' : '' }}>USD · Dólares</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <label class="label-ledger">Monto bruto</label>
                            <input type="number" step="0.01" min="0" name="monto_bruto" value="{{ old('monto_bruto', $recibo->monto_bruto) }}" required class="input-ledger font-mono-num text-lg">
                        </div>
                        <div>
                            <label class="label-ledger">N° continuación</label>
                            <input type="text" name="numero_continuacion" value="{{ old('numero_continuacion', $recibo->numero_continuacion) }}" maxlength="50" class="input-ledger font-mono-num">
                        </div>
                        <div class="flex items-center pt-6">
                            <label class="inline-flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="aplica_retencion" value="1" {{ old('aplica_retencion', $recibo->aplica_retencion) ? 'checked' : '' }} class="w-4 h-4 rounded-none border-[color:var(--rule)] text-ink focus:ring-ink">
                                <span class="text-sm text-ink">Aplicar retención <span class="font-mono-num text-amber-ink">8%</span></span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="label-ledger">Observaciones</label>
                        <textarea name="observaciones" rows="2" class="input-ledger">{{ old('observaciones', $recibo->observaciones) }}</textarea>
                    </div>
                </div>
                <div class="bg-paper px-6 py-4 border-t rule-soft flex items-center justify-between">
                    <a href="{{ route('lotes.show', $recibo->lote_id) }}" class="btn-secondary">Cancelar</a>
                    <button type="submit" class="btn-primary">
                        Actualizar recibo
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
