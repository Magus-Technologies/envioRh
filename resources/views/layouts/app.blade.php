<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistema RHE') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen flex flex-col">

@auth
<nav x-data="{ mobileOpen: false, userOpen: false }" class="bg-paper border-b rule sticky top-0 z-40 backdrop-blur-sm" style="background-color: rgba(250, 248, 243, 0.92);">
    <div class="max-w-[1400px] mx-auto px-5 lg:px-8">
        <div class="flex items-center justify-between h-[72px]">

            <a href="{{ route('dashboard') }}" class="flex items-baseline gap-2.5 group">
                <span class="font-display text-2xl font-semibold text-ink leading-none">envío</span>
                <span class="font-display italic text-2xl text-amber-ink leading-none">RH</span>
                <span class="hidden md:inline-block ml-2 pl-3 border-l rule-soft text-[11px] uppercase tracking-[0.18em] text-ink-3">Sistema SUNAT</span>
            </a>

            <div class="hidden md:flex items-center gap-1">
                @php
                    $navItems = [
                        ['name' => 'Panel', 'route' => 'dashboard', 'active' => 'dashboard'],
                        ['name' => 'Lotes', 'route' => 'lotes.index', 'active' => 'lotes.*'],
                        ['name' => 'Clientes', 'route' => 'clientes.index', 'active' => 'clientes.*'],
                        ['name' => 'Reportes', 'route' => 'reportes.plame', 'active' => 'reportes.*'],
                    ];
                @endphp
                @foreach($navItems as $item)
                    @php $isActive = request()->routeIs($item['active']); @endphp
                    <a href="{{ route($item['route']) }}" class="relative px-4 py-2 text-sm font-medium transition {{ $isActive ? 'text-ink' : 'text-ink-3 hover:text-ink' }}">
                        {{ $item['name'] }}
                        @if($isActive)
                            <span class="absolute bottom-0 left-4 right-4 h-[2px] bg-amber-ink"></span>
                        @endif
                    </a>
                @endforeach
            </div>

            <div class="hidden md:flex items-center gap-4">
                <div class="text-right">
                    <div class="text-[10px] uppercase tracking-[0.14em] text-ink-4 leading-tight">{{ now()->isoFormat('D MMM') }}</div>
                    <div class="text-[11px] text-ink-3 leading-tight font-mono-num">{{ now()->format('H:i') }}</div>
                </div>
                <div class="w-px h-8 bg-[color:var(--rule)]"></div>
                <div class="relative" @click.outside="userOpen = false">
                    <button @click="userOpen = !userOpen" class="flex items-center gap-2 group">
                        <div class="w-9 h-9 bg-ink text-paper rounded-full flex items-center justify-center font-display font-semibold text-sm">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                        <svg class="w-3.5 h-3.5 text-ink-3 transition" :class="userOpen && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="userOpen" x-cloak x-transition.origin.top.right class="absolute right-0 mt-3 w-64 bg-surface border rule rounded-sm overflow-hidden shadow-[0_20px_40px_-12px_rgba(0,0,0,0.08)]">
                        <div class="p-4 border-b rule-soft bg-paper-2">
                            <div class="text-[10px] uppercase tracking-[0.14em] text-ink-4">Sesión iniciada</div>
                            <div class="font-display text-base text-ink mt-0.5">{{ Auth::user()->name }}</div>
                            <div class="text-xs text-ink-3 font-mono-num">{{ Auth::user()->email }}</div>
                        </div>
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-3 text-sm text-ink-2 hover:bg-paper border-b rule-soft">Mi perfil</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-3 text-sm text-clay-ink hover:bg-clay-soft">Cerrar sesión</button>
                        </form>
                    </div>
                </div>
            </div>

            <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2 text-ink">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="mobileOpen ? 'M6 18L18 6M6 6l12 12' : 'M4 6h16M4 12h16M4 18h16'"/></svg>
            </button>
        </div>
    </div>
    <div x-show="mobileOpen" x-cloak class="md:hidden border-t rule">
        @foreach($navItems as $item)
            <a href="{{ route($item['route']) }}" class="block px-5 py-3 text-sm border-b rule-soft {{ request()->routeIs($item['active']) ? 'text-ink font-semibold bg-paper-2' : 'text-ink-3' }}">{{ $item['name'] }}</a>
        @endforeach
        <form method="POST" action="{{ route('logout') }}" class="px-5 py-3">
            @csrf
            <button class="text-sm text-clay-ink">Cerrar sesión</button>
        </form>
    </div>
</nav>
@endauth

@isset($header)
    <header class="bg-surface border-b rule">
        <div class="max-w-[1400px] mx-auto py-6 px-5 lg:px-8">{{ $header }}</div>
    </header>
@endisset

<main class="flex-1">

    @if(session('success'))
        <div class="max-w-[1400px] mx-auto px-5 lg:px-8 mt-5" x-data="{ show: true }" x-show="show" x-transition>
            <div class="flex items-center gap-4 bg-forest-soft border-l-4 border-forest-ink pl-5 pr-4 py-3">
                <span class="kicker" style="color: var(--forest);">Confirmado</span>
                <p class="flex-1 text-sm text-ink">{{ session('success') }}</p>
                <button @click="show = false" class="text-ink-3 hover:text-ink"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-[1400px] mx-auto px-5 lg:px-8 mt-5">
            <div class="flex items-center gap-4 bg-clay-soft border-l-4 border-clay-ink pl-5 pr-4 py-3">
                <span class="kicker" style="color: var(--clay);">Error</span>
                <p class="text-sm text-ink">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="max-w-[1400px] mx-auto px-5 lg:px-8 mt-5">
            <div class="bg-clay-soft border-l-4 border-clay-ink pl-5 pr-4 py-3">
                <span class="kicker" style="color: var(--clay);">Revisa los campos</span>
                <ul class="mt-1.5 text-sm text-ink-2 list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        </div>
    @endif

    @yield('content')
</main>

<footer class="border-t rule bg-paper">
    <div class="max-w-[1400px] mx-auto px-5 lg:px-8 py-5 flex flex-col sm:flex-row justify-between items-center gap-2">
        <div class="flex items-baseline gap-3 text-[12px] text-ink-3">
            <span class="font-display text-ink">envío<span class="italic text-amber-ink">RH</span></span>
            <span class="w-px h-3 bg-[color:var(--rule)]"></span>
            <span>Sistema de Recibos por Honorarios · SUNAT</span>
        </div>
        <div class="flex items-center gap-5 text-[11px] text-ink-4 font-mono-num tracking-wider uppercase">
            <span class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 bg-forest-ink rounded-full"></span>Activo</span>
            <span>v1.0.0</span>
            <span>© {{ date('Y') }}</span>
        </div>
    </div>
</footer>

@stack('scripts')
</body>
</html>
