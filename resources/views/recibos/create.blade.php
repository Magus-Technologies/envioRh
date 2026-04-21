@extends('layouts.app')

@section('title', 'Nuevo Recibo · ' . $lote->codigo_lote)

@section('content')
<div class="max-w-[1400px] mx-auto px-5 lg:px-8 py-10">

    <nav class="text-[11px] uppercase tracking-[0.14em] mb-6 flex items-center gap-2 text-ink-4">
        <a href="{{ route('lotes.index') }}" class="hover:text-amber-ink">Lotes</a>
        <span>/</span>
        <a href="{{ route('lotes.show', $lote->id) }}" class="hover:text-amber-ink font-mono-num normal-case tracking-normal">{{ $lote->codigo_lote }}</a>
        <span>/</span>
        <span class="text-ink">Nuevo recibo</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">

        <div class="lg:col-span-4 fade-up fade-up-1">
            <p class="kicker">Lote <span class="font-mono-num normal-case tracking-normal">{{ $lote->codigo_lote }}</span></p>
            <h1 class="font-display text-5xl font-semibold text-ink mt-2 leading-none">Nuevo <span class="italic">recibo</span>.</h1>
            <p class="mt-4 text-ink-3 text-sm leading-relaxed">Llena los datos del recibo a emitir. La retención se calcula automáticamente si el emisor supera S/ 1,500 acumulado en el mes.</p>

            <div class="mt-8 p-5 bg-ink text-paper">
                <div class="kicker" style="color: var(--gold);">Recordatorio</div>
                <p class="mt-3 text-sm leading-relaxed text-[color:var(--ink-4)]">Este recibo queda en estado <span class="text-paper font-semibold">pendiente</span>. Luego lo emites en SUNAT SOL y regresas al lote para marcarlo como <span class="text-paper font-semibold">EMITIDO</span> con el N° que SUNAT asigna.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('recibos.store', $lote->id) }}" class="lg:col-span-8 fade-up fade-up-2">
            @csrf

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
                                <option value="{{ $td }}" {{ old('emisor_tipo_documento', 'DNI') === $td ? 'selected' : '' }}>{{ $td }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label-ledger">Número</label>
                        <input type="text" name="emisor_numero_documento" value="{{ old('emisor_numero_documento') }}" required maxlength="20" class="input-ledger font-mono-num">
                    </div>
                    <div>
                        <label class="label-ledger">Nombre</label>
                        <input type="text" name="emisor_nombre" value="{{ old('emisor_nombre') }}" required maxlength="255" class="input-ledger">
                    </div>
                </div>
            </div>

            <div class="card-ledger mb-6">
                <div class="p-6 border-b rule-soft">
                    <h2 class="font-display text-xl text-ink">Cliente</h2>
                    <p class="text-xs text-ink-3 mt-1">A quién se le emite el recibo. <a href="{{ route('clientes.create') }}" class="text-amber-ink hover:underline">¿No está registrado?</a></p>
                </div>
                <div class="p-6">
                    <label class="label-ledger">Seleccionar cliente</label>
                    <select name="cliente_id" required class="input-ledger">
                        <option value="">— Seleccionar —</option>
                        @foreach($clientes as $c)
                            <option value="{{ $c->id }}" {{ old('cliente_id') == $c->id ? 'selected' : '' }}>{{ $c->tipo_documento }} {{ $c->numero_documento }} — {{ $c->nombre_razon_social }}</option>
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
                        <input type="text" name="descripcion_servicio" value="{{ old('descripcion_servicio') }}" required maxlength="500" class="input-ledger" placeholder="Ej. Asesoría de inversión — abril 2026">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <label class="label-ledger">Fecha emisión</label>
                            <input type="date" name="fecha_emision" value="{{ old('fecha_emision', now()->format('Y-m-d')) }}" required class="input-ledger font-mono-num">
                        </div>
                        <div>
                            <label class="label-ledger">Fecha vencimiento</label>
                            <input type="date" name="fecha_vencimiento" value="{{ old('fecha_vencimiento') }}" class="input-ledger font-mono-num">
                        </div>
                        <div>
                            <label class="label-ledger">Moneda</label>
                            <select name="moneda" class="input-ledger">
                                <option value="PEN" {{ old('moneda', 'PEN') === 'PEN' ? 'selected' : '' }}>PEN · Soles</option>
                                <option value="USD" {{ old('moneda') === 'USD' ? 'selected' : '' }}>USD · Dólares</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="label-ledger">Monto bruto</label>
                            <input type="number" step="0.01" min="0.01" name="monto_bruto" value="{{ old('monto_bruto') }}" required class="input-ledger font-mono-num text-lg" placeholder="0.00">
                        </div>
                        <div>
                            <label class="label-ledger">N° continuación (opcional)</label>
                            <input type="text" name="numero_continuacion" value="{{ old('numero_continuacion') }}" maxlength="50" class="input-ledger font-mono-num">
                        </div>
                    </div>
                </div>
                <div class="bg-paper px-6 py-4 border-t rule-soft flex items-center justify-between">
                    <a href="{{ route('lotes.show', $lote->id) }}" class="btn-secondary">Cancelar</a>
                    <button type="submit" class="btn-primary">
                        Guardar recibo
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
