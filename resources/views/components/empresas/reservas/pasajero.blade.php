{{-- Alta de pasajeros de taquilla; sus datos se guardan en el pasaje. --}}
<div class="card mb-3">
    <div class="card-header">Agregar pasajero</div>
    <form class="card-body" x-data="formTaquilla('agregarPasajero')" @submit.prevent="enviar" x-ref="form" novalidate>
        <div class="row g-3" x-data="{ tipo: $wire.entangle('pasajero.tipo_pasajero') }">
            <div class="col-md-6">
                <label class="form-label">Nombre</label>
                <input type="text" class="form-control" wire:model="pasajero.nombre" required maxlength="255">
            </div>
            <div class="col-md-6">
                <label class="form-label">Apellido</label>
                <input type="text" class="form-control" wire:model="pasajero.apellido" required maxlength="255">
            </div>
            <div class="col-md-6">
                <label class="form-label">Fecha de nacimiento</label>
                <input type="date" max="{{ today()->toDateString() }}" class="form-control" wire:model="pasajero.fecha_nacimiento" required maxlength="255">
            </div>
            <div class="col-md-6">
                <label class="form-label">Tipo de pasajero</label>
                <select class="form-select" x-model="tipo">
                    <option value="adulto">Adulto</option>
                    <option value="nino">Niño</option>
                    <option value="infante">Infante</option>
                </select>
            </div>
            <div class="col-md-6" x-show="tipo === 'infante'" x-cloak>
                <label class="form-label">Asiento del infante</label>
                <select class="form-select" wire:model.boolean="pasajero.con_asiento">
                    <option value="0">Sin asiento</option>
                    <option value="1">Con asiento</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Tipo de documento</label>
                <select class="form-select" wire:model="pasajero.tipo_documento">
                    <option value="">Sin documento</option>
                    <option value="1">Cédula</option>
                    <option value="2">DNI extranjero</option>
                    <option value="3">Pasaporte</option>
                    <option value="4">Otro</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Documento (opcional)</label>
                <input type="text" class="form-control" wire:model="pasajero.documento_identidad"  maxlength="255">
            </div>
        </div>
        <button class="btn btn-primary mt-3" type="submit" wire:loading.attr="disabled">Agregar pasajero</button>
    </form>
</div>
