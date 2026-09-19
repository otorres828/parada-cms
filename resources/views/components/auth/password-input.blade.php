@props(['hasError','errorMessage'])

<div class="input-group mb-3">

    <div class="input-group-text"><span class="bi bi-lock-fill"></span></div>

    <div class="form-floating">
        <input @class(['form-control','is-invalid'=>$hasError]) placeholder="Ingresar usuario" maxlength="20" {{$attributes}}/>
        <label>Ingresar contraseña</label>
    </div>

    @if ($hasError)

        <div class="invalid-feedback d-block">
            {{ $errorMessage }}

        </div>

    @endif

</div>