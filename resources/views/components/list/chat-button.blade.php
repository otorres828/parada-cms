@props(['evento'])
<a href="{{ route('admin.events.messages', $evento->id) }}" class="btn btn-outline-secondary" title="Chat del evento"
    wire:navigate aria-label="Abrir chat de {{ $evento->nombre }}">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z" />
        <path d="M8 9h8" />
        <path d="M8 13h6" />
    </svg>
</a>
