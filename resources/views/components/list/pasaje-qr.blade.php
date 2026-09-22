{{-- Botón de escaneo y modal SweetAlert del QR, disponible solo para pasajes pagados. --}}
@props(['pasaje'])

@php($qr = $pasaje->getQr())

@if ($qr)
    <div x-data>

        <button type="button" class="btn btn-outline-secondary btn-sm"
            data-code="{{ $pasaje->id }}"
            @click="Swal.fire({
                titleText: 'Pase QR: #' + $el.dataset.code,
                html: $refs.contenidoQr.innerHTML,
                showCloseButton: true,
                showConfirmButton: false,
                customClass: {
                    popup: 'rounded-4 shadow-lg'
                }
            })"
            title="Ver QR del pasaje" aria-label="Ver QR del pasaje #{{ $pasaje->id }}">
            <i class="bi bi-qr-code-scan" aria-hidden="true"></i>
        </button>

        <template x-ref="contenidoQr">

            <div class="d-flex justify-content-center p-3">
                <div class="bg-white p-3 border rounded shadow-sm">
                    <img src="{{ $qr }}" alt="Código QR del pasaje #{{ $pasaje->id }}"
                        style="width: 200px; height: 200px;" class="img-fluid">
                </div>
            </div>

            <p class="mt-2 text-muted small">Muestre este código al abordar el autobús.</p>

        </template>

    </div>
@else
    <span class="text-body-secondary">—</span>
@endif
