@props(['icon', 'margin', 'type', 'value','hasError','errorMessage', 'isRequired'])

<div class="mb-{{ $margin ?? 3 }}">

    @if (strlen($slot)>0)

        <label class="form-label mb-1">
            {{ $slot }}@if (isset($isRequired))<small class="text-danger fst-italic">*</small>@endif
        </label>

    @endif

    <div class="input-group">

        @isset($icon)
    
            <div class="input-group-text">
                <span class="bi bi-{{ $icon }}"></span>
            </div>

        @endisset

        <input type="{{ $type ?? 'text' }}" @class(['form-control','is-invalid'=>isset($hasError)]) {{ $attributes }}
        value="{{ $value ?? '' }}"/>

        @if (isset($hasError))

        <div class="invalid-feedback d-block">
            {{ $errorMessage }}
        </div>

        @endif

    </div>
</div>