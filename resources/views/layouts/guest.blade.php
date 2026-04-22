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
                <h1 class="font-display text-5xl lg:text-6xl font-semibold leading-[1.05]" style="color: #faf8f3;">
                    Emite tus recibos <span class="italic" style="color: var(--amber);">por honorarios</span>.
                </h1>
            </div>

            <div></div>
        </aside>

        <main class="flex items-center justify-center p-8 lg:p-16 bg-paper relative">
            <div class="absolute top-8 right-8 text-right">
                <p class="text-[10px] uppercase tracking-[0.18em] text-ink-4">{{ now()->isoFormat('dddd, D [de] MMMM') }}</p>
                <p class="font-mono-num text-xs text-ink-3 mt-0.5">{{ now()->format('H:i') }} · Lima</p>
            </div>

            <div class="w-full max-w-md fade-up">
                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>
