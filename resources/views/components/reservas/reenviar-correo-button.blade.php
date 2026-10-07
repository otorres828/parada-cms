<button
    type="button"
    class="btn btn-outline-primary me-2"
    wire:loading.attr="disabled"
    wire:target="reenviarCorreo"
    @click="Swal.fire({
        title: '¿Reenviar el correo?',
        text: 'Se enviará al comprador el comprobante de la reserva, los pasajes y sus QR.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, reenviar',
        cancelButtonText: 'Cancelar',
        buttonsStyling: false,
        customClass: {
            confirmButton: 'btn btn-primary me-2',
            cancelButton: 'btn btn-secondary'
        }
    }).then(result => { if (result.isConfirmed) $wire.reenviarCorreo(); })"
>
    <i class="bi bi-envelope me-1"></i> Reenviar correo
</button>
