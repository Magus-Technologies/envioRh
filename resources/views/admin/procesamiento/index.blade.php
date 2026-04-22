@extends('layouts.app')

@section('title', 'Procesamiento SUNAT · Admin')

@section('content')
<div class="max-w-[1400px] mx-auto px-5 lg:px-8 py-10">

    <nav class="text-[11px] uppercase tracking-[0.14em] mb-6 flex items-center gap-2 text-ink-4">
        <span class="text-ink">Admin</span>
        <span>/</span>
        <span class="text-ink">Procesamiento SUNAT</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-end mb-10 pb-6 border-b-2 border-ink">
        <div class="lg:col-span-8">
            <p class="kicker">Panel Administrador</p>
            <h1 class="font-display text-5xl font-semibold text-ink mt-2 leading-none">Procesamiento <span class="italic">SUNAT</span>.</h1>
            <p class="mt-3 text-ink-3 max-w-xl">Recibos enviados por clientes. Emítelos manualmente en SUNAT SOL, luego sube el PDF y registra el N° asignado.</p>
        </div>
        <div class="lg:col-span-4 flex lg:justify-end">
            <div class="flex gap-6">
                <div>
                    <div class="kicker !text-ink-3">En cola</div>
                    <div class="font-display text-4xl font-light text-amber-ink mt-1 tabular">{{ str_pad($contadores['en_cola'], 2, '0', STR_PAD_LEFT) }}</div>
                </div>
                <div>
                    <div class="kicker !text-ink-3">Procesados</div>
                    <div class="font-display text-4xl font-light text-forest-ink mt-1 tabular">{{ str_pad($contadores['procesado'], 2, '0', STR_PAD_LEFT) }}</div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 border border-forest-ink bg-forest-ink/5 text-forest-ink text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 border border-clay-ink bg-clay-ink/5 text-clay-ink text-sm">{{ session('error') }}</div>
    @endif

    <div class="flex gap-1 mb-6 border-b rule">
        <a href="{{ route('admin.procesamiento.index', ['estado' => 'en_cola']) }}"
           class="px-5 py-3 text-xs uppercase tracking-wider border-b-2 {{ $estado === 'en_cola' ? 'border-amber-ink text-ink' : 'border-transparent text-ink-3 hover:text-ink' }}">
            En cola ({{ $contadores['en_cola'] }})
        </a>
        <a href="{{ route('admin.procesamiento.index', ['estado' => 'procesado']) }}"
           class="px-5 py-3 text-xs uppercase tracking-wider border-b-2 {{ $estado === 'procesado' ? 'border-amber-ink text-ink' : 'border-transparent text-ink-3 hover:text-ink' }}">
            Procesados ({{ $contadores['procesado'] }})
        </a>
    </div>

    <div class="card-ledger overflow-hidden">
        @if($recibos->count() > 0)
            <div class="overflow-x-auto">
                <table class="table-ledger">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Cliente (user)</th>
                            <th>Lote</th>
                            <th>Emisor</th>
                            <th>Receptor</th>
                            <th>Descripción</th>
                            <th class="text-right">Monto</th>
                            @if($estado === 'en_cola')
                                <th class="text-right">Procesar</th>
                            @else
                                <th class="text-right">PDF / N°</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recibos as $recibo)
                        <tr x-data="{ open: false, num: '', pdf: null }">
                            <td class="font-mono-num text-ink-4">{{ str_pad($recibo->id, 3, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                <div class="text-ink text-sm">{{ $recibo->lote?->user?->name ?? '—' }}</div>
                                <div class="text-[11px] text-ink-4">{{ $recibo->lote?->user?->email ?? '' }}</div>
                            </td>
                            <td class="font-mono-num text-xs text-ink-3">{{ $recibo->lote?->codigo_lote ?? '—' }}</td>
                            <td>
                                <div class="text-ink text-sm">{{ \Illuminate\Support\Str::limit($recibo->emisor_nombre, 22) }}</div>
                                <div class="text-[11px] text-ink-4 font-mono-num">{{ $recibo->emisor_tipo_documento }} {{ $recibo->emisor_numero_documento }}</div>
                            </td>
                            <td>
                                <div class="text-ink text-sm">{{ \Illuminate\Support\Str::limit($recibo->cliente->nombre_razon_social, 22) }}</div>
                                <div class="text-[11px] text-ink-4 font-mono-num">{{ $recibo->cliente->tipo_documento }} {{ $recibo->cliente->numero_documento }}</div>
                            </td>
                            <td class="text-ink-3 text-sm">{{ \Illuminate\Support\Str::limit($recibo->descripcion_servicio, 28) }}</td>
                            <td class="text-right font-mono-num text-ink">{{ number_format($recibo->monto_bruto, 2) }}</td>

                            @if($estado === 'en_cola')
                                <td class="text-right">
                                    <button type="button" @click="open = true" class="btn-primary text-xs">
                                        Procesar
                                    </button>

                                    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60" @click.self="open = false">
                                        <form method="POST" action="{{ route('admin.procesamiento.procesar', $recibo->id) }}"
                                              enctype="multipart/form-data"
                                              class="bg-paper max-w-lg w-full mx-4 p-7 rounded-sm shadow-xl text-left">
                                            @csrf
                                            <h3 class="font-display text-2xl font-semibold text-ink mb-1">Procesar recibo #{{ $recibo->id }}</h3>
                                            <p class="text-xs text-ink-3 mb-5">
                                                Emitido manualmente en SUNAT SOL. Sube el PDF que descargaste y registra el N° asignado.
                                            </p>

                                            <div class="mb-4 p-3 bg-paper-2 border rule-soft text-xs space-y-1">
                                                <div><span class="text-ink-4">Emisor:</span> {{ $recibo->emisor_nombre }} ({{ $recibo->emisor_numero_documento }})</div>
                                                <div><span class="text-ink-4">Receptor:</span> {{ $recibo->cliente->nombre_razon_social }}</div>
                                                <div><span class="text-ink-4">Monto:</span> S/ {{ number_format($recibo->monto_bruto, 2) }}</div>
                                                <div><span class="text-ink-4">Concepto:</span> {{ \Illuminate\Support\Str::limit($recibo->descripcion_servicio, 80) }}</div>
                                            </div>

                                            <label class="label-ledger">N° Recibo SUNAT</label>
                                            <input type="text" name="numero_recibo_sunat" x-model="num" required maxlength="50"
                                                   class="input-ledger font-mono-num" placeholder="E001-123">

                                            <label class="label-ledger mt-4">PDF emitido por SUNAT</label>
                                            <input type="file" name="archivo_pdf" accept="application/pdf" required
                                                   class="w-full text-xs border border-rule-strong p-2 file:mr-3 file:px-3 file:py-1 file:border-0 file:bg-ink file:text-paper">
                                            <p class="text-[10px] text-ink-4 mt-1">Máx. 5 MB. Solo PDF.</p>

                                            <div class="flex gap-2 justify-end mt-6">
                                                <button type="button" @click="open = false" class="btn-secondary text-xs">Cancelar</button>
                                                <button type="submit" class="btn-primary text-xs">Marcar procesado</button>
                                            </div>
                                        </form>
                                    </div>
                                </td>
                            @else
                                <td class="text-right text-xs space-x-3">
                                    <span class="text-ink-4 font-mono-num">{{ $recibo->numero_recibo_sunat }}</span>
                                    <a href="{{ route('recibos.pdf', $recibo->id) }}" target="_blank" class="text-forest-ink hover:text-ink uppercase tracking-wider">PDF</a>
                                </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($recibos->hasPages())
                <div class="p-4 border-t rule-soft bg-paper">{{ $recibos->links() }}</div>
            @endif
        @else
            <div class="py-20 text-center">
                <p class="font-display italic text-2xl text-ink-3">
                    @if($estado === 'en_cola')
                        No hay recibos en cola de procesamiento.
                    @else
                        Aún no se ha procesado ningún recibo.
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>
@endsection
