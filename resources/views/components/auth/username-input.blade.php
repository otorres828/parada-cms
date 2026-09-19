@props(['hasError','errorMessage'])

<div class="input-group mb-2">

    <div class="input-group-text"><span class="bi bi-person-fill"></span></div>

    <div class="form-floating">
        <input {{$attributes}} type="text" @class(['form-control','is-invalid'=>$hasError]) placeholder="Ingresar usuario" maxlength="40"/>
        <label>Ingresar usuario</label>
    </div>

    @if ($hasError)

        <div class="invalid-feedback d-block">
            {{ $errorMessage }}

        </div>

    @endif


</div>
