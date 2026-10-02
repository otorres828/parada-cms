{{-- Pasajes de la venta de taquilla y QR disponibles tras confirmar el pago. --}}
@props(['reserva', 'editable'])
<div class="card mb-3">
    <div class="card-header">Pasajeros de la reserva</div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Pasajero</th>
                    <th>Documento</th>
                    <th>Asiento</th>
                    <th>Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reserva->pasajes as $pasaje)
                    <tr wire:key="taquilla-pasaje-{{ $pasaje->id }}">
                        <td>{{ $pasaje->viajero_nombre_completo }}</td>
                        <td>{{ $pasaje->viajero_documento ?? 'Sin documento' }}</td>
                        <td>{{ $pasaje->numero_asiento ?? 'Sin asiento' }}</td>
                        <td><x-money.dual :usd="$pasaje->total" :bs="$pasaje->calcularMontoBs($pasaje->total)" /></td>
                        <td class="text-end">
                            @if ($editable)
                                <button class="btn btn-outline-danger btn-sm"
                                    wire:click="removerPasajero({{ $pasaje->id }})"
                                    wire:loading.attr="disabled">Retirar</button>
                            @elseif ($reserva->estado_pago === \App\Models\Reserva::ESTADO_PAGO_PAGADO)
                                <img src="{{ $pasaje->getQr() }}" width="150" height="150"
                                    alt="QR del pasaje {{ $pasaje->id }}">
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">Agrega al menos un pasajero para registrar el pago.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
