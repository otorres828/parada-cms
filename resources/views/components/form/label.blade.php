@props(['margin'])

<label class="form-label mb-{{ $margin ?? 1 }}">
    {{ $slot }}
</label>

    