@extends('layouts.app')

@section('title', 'Nuevo Cliente')

@section('content')
<div class="max-w-[1400px] mx-auto px-5 lg:px-8 py-10">

    <nav class="text-[11px] uppercase tracking-[0.14em] mb-6 flex items-center gap-2 text-ink-4">
        <a href="{{ route('clientes.index') }}" class="hover:text-amber-ink">Clientes</a>
        <span>/</span>
        <span class="text-ink">Nuevo</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
        <div class="lg:col-span-5 fade-up fade-up-1">
            <p class="kicker">Registro</p>
            <h1 class="font-display text-5xl font-semibold text-ink mt-2 leading-none">Nuevo <span class="italic">cliente</span>.</h1>
            <p class="mt-5 text-ink-3 leading-relaxed">Los clientes son las empresas o personas a las que emites recibos por honorarios. Podrás seleccionarlos al crear o editar un recibo.</p>
        </div>
        <form method="POST" action="{{ route('clientes.store') }}" class="lg:col-span-7 fade-up fade-up-2">
            @include('clientes._form')
        </form>
    </div>
</div>
@endsection
