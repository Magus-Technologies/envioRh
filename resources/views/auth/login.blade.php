<x-guest-layout>
    <div class="mb-10">
        <p class="kicker">Acceso</p>
        <h2 class="font-display text-4xl font-semibold text-ink mt-2 leading-[1.1]">Ingresa a tu <span class="italic">cuenta</span>.</h2>
        <p class="mt-3 text-sm text-ink-3">Escribe tu correo y contraseña para continuar trabajando.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="label-ledger">Correo electrónico</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="tu@correo.com" class="input-ledger font-mono-num">
            @error('email')<p class="mt-1.5 text-xs text-clay-ink">{{ $message }}</p>@enderror
        </div>

        <div x-data="{ show: false }">
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="label-ledger !mb-0">Contraseña</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-[11px] text-ink-3 hover:text-amber-ink underline underline-offset-2">¿Olvidaste?</a>
                @endif
            </div>
            <div class="relative">
                <input id="password" name="password" :type="show ? 'text' : 'password'" type="password" required autocomplete="current-password" placeholder="••••••••" class="input-ledger pr-10">
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 px-3 text-ink-3 hover:text-ink">
                    <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg x-show="show" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411"/></svg>
                </button>
            </div>
            @error('password')<p class="mt-1.5 text-xs text-clay-ink">{{ $message }}</p>@enderror
        </div>

        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="remember" class="w-4 h-4 rounded-none border-[color:var(--rule)] text-ink focus:ring-ink">
            <span class="text-sm text-ink-2">Mantener sesión iniciada</span>
        </label>

        <button type="submit" class="w-full btn-primary justify-center py-3.5 text-base">
            Ingresar al sistema
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </button>
    </form>

    <div class="mt-10 pt-6 border-t rule-soft flex items-center justify-between">
        <p class="text-xs text-ink-3">¿Primera vez aquí?</p>
        @if (Route::has('register'))
            <a href="{{ route('register') }}" class="text-xs font-semibold text-amber-ink hover:text-ink">Crear cuenta →</a>
        @endif
    </div>
</x-guest-layout>
