{{-- Confirmación de reserva con el contenedor adaptable de Laravel. --}}
<x-mail::message>
<x-mail::reserva-comprobante
    :reserva="$reserva"
    :qrs="$qrs"
    :message="$message"
/>
</x-mail::message>
