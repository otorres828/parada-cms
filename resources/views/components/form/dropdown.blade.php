@props(['icon', 'margin', 'label','hasError','errorMessage', 'isRequired'])

<div class="mb-{{ $margin ?? 3 }}">

    @isset($label)

        <label class="form-label mb-1">
            <b>{{ $label }}</b> @if (isset($isRequired))<small class="text-danger fst-italic">*</small>@endif
        </label>

    @endisset

    <div class="input-group">

        <div class="input-group-text">
            <span class="bi bi-{{ $icon ?? '' }}"></span>
        </div>

        <select @class(['form-select','is-invalid'=>isset($hasError)]) {{ $attributes }}>
            {{ $slot ?? ''}}
        </select>

        @if (isset($hasError))

        <div class="invalid-feedback d-block">
            {{ $errorMessage }}

        </div>

        @endif

    </div>

</div>
