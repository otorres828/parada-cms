{{-- Datos de contacto del comprador de taquilla. No crea una cuenta. --}}
@props(['habilitado' => false])
<div class="card mb-3">
    <div class="card-header">2. Datos del comprador</div>
    <div class="card-body">
        @unless ($habilitado)
            <p class="text-muted small">Selecciona una salida para completar los datos del comprador.</p>
        @endunless
        <fieldset class="border-0 p-0 m-0" @disabled(! $habilitado)>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="taquilla-comprador-nombre">Nombre completo</label>
                    <input id="taquilla-comprador-nombre" form="registrar-taquilla" type="text" class="form-control" wire:model="comprador.nombre" required maxlength="255">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="taquilla-comprador-telefono">Teléfono</label>
                    <input id="taquilla-comprador-telefono" form="registrar-taquilla" type="tel" class="form-control" wire:model="comprador.telefono" required maxlength="255" onkeypress="return /^[0-9+\-\s]+$/.test(event.key)">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="taquilla-comprador-email">Correo electrónico (opcional)</label>
                    <input id="taquilla-comprador-email" form="registrar-taquilla" type="email" class="form-control" wire:model="comprador.email"  maxlength="255">
                </div>
            </div>
        </fieldset>
    </div>
</div>
