@props(['margin'])

<small class="d-block mb-{{ $margin ?? 0 }}">
    {{ $slot}}
</small>