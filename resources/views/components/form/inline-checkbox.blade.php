@php
    $id = 'id-'.rand(1, 9000);
@endphp

<div class="form-check form-check-inline">
    <input class="form-check-input" type="checkbox" id="{{ $id }}" {{ $attributes }}>
    <label class="form-check-label" for="{{ $id }}">
        {{ $slot }}
    </label>
</div>