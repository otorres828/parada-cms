@props(['margin','ready'=>false])

@php
    $id = 'id-'.rand(1, 9000);
@endphp

<div class="form-check form-switch mb-{{ $margin ?? 1 }}">
    <input class="form-check-input" type="checkbox" role="switch" id="{{ $id }}" {{ $attributes }}
    @if ($ready ?? false)
        checked
    @endif
    >
    <label class="form-check-label" for="{{ $id }}">
        {{ $slot }}
    </label>
</div>
