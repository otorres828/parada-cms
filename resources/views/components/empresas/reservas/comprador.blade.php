{{-- Datos de contacto del comprador de taquilla. No crea una cuenta. --}}
<div class="card mb-3">
    <div class="card-header">Comprador</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nombre completo</label>
                <input form="registrar-taquilla" type="text" class="form-control" wire:model="comprador.nombre" required maxlength="255">
            </div>
            <div class="col-md-6">
                <label class="form-label">Teléfono</label>
                <input form="registrar-taquilla" type="tel" class="form-control" wire:model="comprador.telefono" required maxlength="255">
            </div>
            <div class="col-md-6">
                <label class="form-label">Correo electrónico (opcional)</label>
                <input form="registrar-taquilla" type="email" class="form-control" wire:model="comprador.email"  maxlength="255">
            </div>
        </div>
    </div>
</div>
