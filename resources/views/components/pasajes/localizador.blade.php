@props(['qr','pasaje'])
<div class="col-md-6">

    <div class="card">

        <div class="card-header">Código QR del pasaje</div>

        <div class="card-body text-center">

            <img src="{{ $qr }}" alt="Código QR del pasaje #{{ $pasaje->id }}" width="256" height="256"
                class="img-fluid bg-white">

            <p class="small text-body-secondary text-break mt-3 mb-0">
                {{ $pasaje->localizador }}
            </p>

        </div>

    </div>

</div>
