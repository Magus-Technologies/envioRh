@extends('layouts.app')

@section('title', 'Editar Cliente')

@section('content')
<div class="max-w-[1400px] mx-auto px-5 lg:px-8 py-10">

    <nav class="text-[11px] uppercase tracking-[0.14em] mb-6 flex items-center gap-2 text-ink-4">
        <a href="{{ route('clientes.index') }}" class="hover:text-amber-ink">Clientes</a>
        <span>/</span>
        <a href="{{ route('clientes.show', $cliente->id) }}" class="hover:text-amber-ink normal-case tracking-normal">{{ Str::limit($cliente->nombre_razon_social, 25) }}</a>
        <span>/</span>
        <span class="text-ink">Editar</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
        <div class="lg:col-span-5 fade-up fade-up-1">
            <p class="kicker">Edición</p>
            <h1 class="font-display text-5xl font-semibold text-ink mt-2 leading-none">Editar <span class="italic">cliente</span>.</h1>
            <p class="mt-3 text-ink-3">{{ $cliente->nombre_razon_social }}</p>
            <p class="font-mono-num text-xs text-ink-4 mt-1">{{ $cliente->tipo_documento }} {{ $cliente->numero_documento }}</p>
        </div>
        <form method="POST" action="{{ route('clientes.update', $cliente->id) }}" class="lg:col-span-7 fade-up fade-up-2">
            @method('PUT')
            @include('clientes._form')
        </form>
    </div>
</div>
@endsection
