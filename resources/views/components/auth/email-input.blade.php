@props(['hasError','errorMessage'])

<div class="input-group mb-3">

    <div class="input-group-text"><span class="bi bi-envelope-fill"></span></div>

    <div class="form-floating">
                <label>Ingresar email</label>
        <input {{$attributes}} type="text" @class(['form-control','is-invalid'=>$hasError]) placeholder="Ingresar email" maxlength="50" name="email"/>
    </div>

    @if ($hasError)

        <div class="invalid-feedback d-block">
            {{ $errorMessage }}

        </div>

    @endif

</div>
