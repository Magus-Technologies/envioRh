<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} — Ingreso</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="min-h-screen grid lg:grid-cols-2">

        <aside class="hidden lg:flex flex-col justify-between p-12 relative overflow-hidden bg-ink text-paper">
            <div class="absolute inset-0 opacity-[0.06]" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 14px 14px;"></div>
            <div class="absolute -right-32 -bottom-32 w-[500px] h-[500px] rounded-full" style="background: radial-gradient(circle, rgba(184,115,47,0.25) 0%, transparent 70%);"></div>

            <div class="relative z-10">
                <div class="flex items-baseline gap-2">
                    <span class="font-display text-3xl font-semibold">envío</span>
                    <span class="font-display italic text-3xl" style="color: var(--amber);">RH</span>
                </div>
                <p class="mt-1 text-[11px] uppercase tracking-[0.22em] text-[color:var(--ink-4)]">Sistema SUNAT · Perú</p>
            </div>

            <div class="relative z-10 max-w-md">
                <h1 class="font-display text-5xl lg:text-6xl font-semibold leading-[1.05]">
                    Emite tus recibos <span class="italic" style="color: var(--amber);">por honorarios</span> sin salir de casa.
                </h1>
                <p class="mt-6 text-base text-[color:var(--ink-4)] leading-relaxed">
                    Genera archivos PLAME, calcula retenciones del 8% y mantén tu cartera de clientes al día. Diseñado para contadores y profesionales independientes.
                </p>
            </div>

            <div class="relative z-10 grid grid-cols-3 gap-6 pt-8 border-t" style="border-color: rgba(255,255,255,0.08);">
                <div>
                    <div class="font-mono-num text-3xl font-light">08<span class="text-base">%</span></div>
                    <div class="text-[10px] uppercase tracking-wider text-[color:var(--ink-4)] mt-1">Retención</div>
                </div>
                <div>
                    <div class="font-mono-num text-3xl font-light">1,500</div>
                    <div class="text-[10px] uppercase tracking-wider text-[color:var(--ink-4)] mt-1">Tope mensual</div>
                </div>
                <div>
                    <div class="font-mono-num text-3xl font-light">PLAME</div>
                    <div class="text-[10px] uppercase tracking-wider text-[color:var(--ink-4)] mt-1">Formato</div>
                </div>
            </div>
        </aside>

        <main class="flex items-center justify-center p-8 lg:p-16 bg-paper relative">
            <div class="absolute top-8 right-8 text-right">
                <p class="text-[10px] uppercase tracking-[0.18em] text-ink-4">{{ now()->isoFormat('dddd, D [de] MMMM') }}</p>
                <p class="font-mono-num text-xs text-ink-3 mt-0.5">{{ now()->format('H:i') }} · Lima</p>
            </div>

            <div class="w-full max-w-md fade-up">
                {{ $slot }}
            </div>

            <div class="absolute bottom-8 left-8 right-8 flex justify-between items-center text-[11px] text-ink-4">
                <span class="font-mono-num">© {{ date('Y') }} envíoRH</span>
                <span>v1.0.0</span>
            </div>
        </main>
    </div>
</body>
</html>
