@props(['icon', 'margin', 'value', 'hasError', 'errorMessage', 'isRequired', 'rows'])

<div class="mb-{{ $margin ?? 3 }}">

    @if (strlen($slot) > 0)
        <label class="form-label mb-1">
            {{ $slot }}@if (isset($isRequired))<small class="text-danger fst-italic">*</small>@endif
        </label>
    @endif

    <div class="input-group">

        @isset($icon)
            <div class="input-group-text align-items-start pt-2">
                <span class="bi bi-{{ $icon }}"></span>
            </div>
        @endisset

        {{-- Cambiado a textarea. El valor (value) se renderiza en el cuerpo de la etiqueta, no como atributo --}}
        <textarea
            rows="{{ $rows ?? 3 }}"
            @class(['form-control', 'is-invalid' => isset($hasError)])
            {{ $attributes }}>{{ $value ?? '' }}</textarea>

        @if (isset($hasError))
            <div class="invalid-feedback d-block">
                {{ $errorMessage }}
            </div>
        @endif

    </div>
</div>
