@extends('layouts.app')

@section('title', 'Clientes')

@section('content')
<div class="max-w-[1400px] mx-auto px-5 lg:px-8 py-10">

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-end mb-10 pb-6 border-b-2 border-ink">
        <div class="lg:col-span-8">
            <p class="kicker">Cartera · {{ $clientes->total() ?? $clientes->count() }} registros</p>
            <h1 class="font-display text-5xl font-semibold text-ink mt-2 leading-none">Clientes</h1>
            <p class="mt-3 text-ink-3">Empresas y personas naturales a quienes emites recibos por honorarios.</p>
        </div>
        <div class="lg:col-span-4 flex lg:justify-end">
            <a href="{{ route('clientes.create') }}" class="btn-primary">
                <span>+</span> Nuevo cliente
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('clientes.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-8 p-5 bg-surface border rule">
        <div class="md:col-span-3">
            <label class="label-ledger">Buscar</label>
            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Nombre, razón social, RUC o DNI…" class="input-ledger">
        </div>
        <div class="md:col-span-2">
            <label class="label-ledger">Estado</label>
            <select name="estado" class="input-ledger">
                <option value="">Todos</option>
                <option value="activo" {{ request('estado') === 'activo' ? 'selected' : '' }}>Activo</option>
                <option value="inactivo" {{ request('estado') === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="btn-primary flex-1 justify-center">Filtrar</button>
        </div>
    </form>

    <div class="card-ledger overflow-hidden">
        @if($clientes->count() > 0)
            <div class="overflow-x-auto">
                <table class="table-ledger">
                    <thead>
                        <tr>
                            <th style="width: 50px;">—</th>
                            <th>Documento</th>
                            <th>Nombre / Razón social</th>
                            <th>Contacto</th>
                            <th>Estado</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clientes as $cliente)
                        <tr>
                            <td>
                                <div class="w-9 h-9 rounded-full bg-paper-2 border rule flex items-center justify-center font-display text-sm text-ink">{{ strtoupper(substr($cliente->nombre_razon_social, 0, 1)) }}</div>
                            </td>
                            <td>
                                <div class="text-[10px] uppercase tracking-wider text-ink-4">{{ $cliente->tipo_documento }}</div>
                                <div class="font-mono-num text-ink">{{ $cliente->numero_documento }}</div>
                            </td>
                            <td>
                                <a href="{{ route('clientes.show', $cliente->id) }}" class="font-display text-base text-ink hover:text-amber-ink">{{ $cliente->nombre_razon_social }}</a>
                                @if($cliente->actividad_economica)
                                    <div class="text-xs text-ink-4 mt-0.5">{{ Str::limit($cliente->actividad_economica, 45) }}</div>
                                @endif
                            </td>
                            <td class="text-xs">
                                @if($cliente->email)<div class="text-ink-3 font-mono-num">{{ $cliente->email }}</div>@endif
                                @if($cliente->telefono)<div class="text-ink-4 font-mono-num">{{ $cliente->telefono }}</div>@endif
                                @if(!$cliente->email && !$cliente->telefono)<div class="text-ink-4">—</div>@endif
                            </td>
                            <td>
                                <span class="badge {{ $cliente->estado === 'activo' ? 'badge-forest' : 'badge-ink' }}">{{ $cliente->estado }}</span>
                            </td>
                            <td class="text-right text-xs space-x-3 uppercase tracking-wider">
                                <a href="{{ route('clientes.show', $cliente->id) }}" class="text-ink hover:text-amber-ink">Ver</a>
                                <a href="{{ route('clientes.edit', $cliente->id) }}" class="text-ink hover:text-amber-ink">Editar</a>
                                <form action="{{ route('clientes.destroy', $cliente->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar cliente?')">
                                    @csrf @method('DELETE')
                                    <button class="text-clay-ink hover:text-ink">Borrar</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(method_exists($clientes, 'links') && $clientes->hasPages())
                <div class="p-4 border-t rule-soft bg-paper">{{ $clientes->links() }}</div>
            @endif
        @else
            <div class="py-20 text-center">
                <p class="font-display italic text-3xl text-ink-3">Sin clientes registrados.</p>
                <a href="{{ route('clientes.create') }}" class="btn-primary mt-5">Registrar primer cliente</a>
            </div>
        @endif
    </div>
</div>
@endsection
