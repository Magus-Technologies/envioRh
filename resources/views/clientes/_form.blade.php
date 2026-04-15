@csrf
<div class="card-ledger" x-data="clienteLookup({
    tipo: '{{ old('tipo_documento', $cliente->tipo_documento ?? 'DNI') }}',
    numero: '{{ old('numero_documento', $cliente->numero_documento ?? '') }}',
    nombre: @js(old('nombre_razon_social', $cliente->nombre_razon_social ?? '')),
    direccion: @js(old('direccion', $cliente->direccion ?? '')),
    actividad: @js(old('actividad_economica', $cliente->actividad_economica ?? ''))
})">
    <div class="p-6 border-b rule-soft flex items-center justify-between">
        <h2 class="font-display text-xl text-ink">Datos del cliente</h2>
        <div class="flex items-center gap-3 text-[11px] uppercase tracking-wider" x-show="status" x-transition>
            <span x-show="loading" class="flex items-center gap-2 text-amber-ink">
                <svg class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2.5" opacity="0.25"/><path stroke-linecap="round" stroke-width="2.5" d="M21 12a9 9 0 0 0-9-9"/></svg>
                Consultando…
            </span>
            <span x-show="status === 'ok' && !loading" class="text-forest-ink">✓ Datos obtenidos</span>
            <span x-show="status === 'error' && !loading" class="text-clay-ink" x-text="errorMsg"></span>
        </div>
    </div>
    <div class="p-6 space-y-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="label-ledger">Tipo de documento</label>
                <select name="tipo_documento" required class="input-ledger" x-model="tipo">
                    @foreach(['DNI','RUC','CE','Pasaporte'] as $td)
                        <option value="{{ $td }}">{{ $td }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label-ledger flex items-center justify-between">
                    <span>Número de documento</span>
                    <button type="button"
                            @click="buscar()"
                            x-show="(tipo === 'DNI' && numero.length === 8) || (tipo === 'RUC' && numero.length === 11)"
                            class="text-[10px] uppercase tracking-wider text-amber-ink hover:text-ink">
                        Consultar
                    </button>
                </label>
                <input type="text" name="numero_documento" required maxlength="20"
                       class="input-ledger font-mono-num"
                       x-model="numero"
                       @blur="autoLookup()"
                       @keyup.enter.prevent="buscar()">
            </div>
        </div>
        <div>
            <label class="label-ledger">Nombre o razón social</label>
            <input type="text" name="nombre_razon_social" required maxlength="255" class="input-ledger" x-model="nombre">
        </div>
        <div>
            <label class="label-ledger">Dirección</label>
            <input type="text" name="direccion" maxlength="255" class="input-ledger" x-model="direccion">
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="label-ledger">Correo electrónico</label>
                <input type="email" name="email" value="{{ old('email', $cliente->email ?? '') }}" maxlength="100" class="input-ledger font-mono-num">
            </div>
            <div>
                <label class="label-ledger">Teléfono</label>
                <input type="text" name="telefono" value="{{ old('telefono', $cliente->telefono ?? '') }}" maxlength="50" class="input-ledger font-mono-num">
            </div>
        </div>
        <div>
            <label class="label-ledger">Actividad económica</label>
            <input type="text" name="actividad_economica" maxlength="255" class="input-ledger" x-model="actividad">
        </div>
        <div class="max-w-xs">
            <label class="label-ledger">Estado</label>
            <select name="estado" class="input-ledger">
                <option value="activo" {{ old('estado', $cliente->estado ?? 'activo') === 'activo' ? 'selected' : '' }}>Activo</option>
                <option value="inactivo" {{ old('estado', $cliente->estado ?? '') === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
            </select>
        </div>
    </div>
    <div class="bg-paper px-6 py-4 border-t rule-soft flex items-center justify-between">
        <a href="{{ route('clientes.index') }}" class="btn-secondary">Cancelar</a>
        <button type="submit" class="btn-primary">
            Guardar cliente
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </button>
    </div>
</div>

<script>
    function clienteLookup(init) {
        return {
            tipo: init.tipo,
            numero: init.numero,
            nombre: init.nombre,
            direccion: init.direccion,
            actividad: init.actividad,
            loading: false,
            status: '',
            errorMsg: '',
            autoLookup() {
                if (this.tipo === 'DNI' && this.numero.length === 8) this.buscar();
                else if (this.tipo === 'RUC' && this.numero.length === 11) this.buscar();
            },
            async buscar() {
                if (this.loading) return;
                this.loading = true;
                this.status = '';
                this.errorMsg = '';
                const res = await window.consultarDocumento(this.numero);
                this.loading = false;
                if (!res.success) {
                    this.status = 'error';
                    this.errorMsg = res.message;
                    return;
                }
                if (this.numero.length === 8) {
                    this.nombre = res.data.nombreCompleto;
                } else {
                    this.nombre = res.data.razonSocial;
                    if (res.data.direccion) this.direccion = res.data.direccion;
                }
                this.status = 'ok';
            }
        };
    }
</script>
