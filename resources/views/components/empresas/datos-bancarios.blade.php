{{--
    DATOS BANCARIOS DE EMPRESA | Lista las cuentas bancarias y Pago Móvil asociados a la agencia.
--}}

@props(['datosBancarios'])

<div class="card">

    <div class="card-header">
        <strong>Datos bancarios</strong>
    </div>

    <div class="table-responsive">

        <table class="table table-hover align-middle mb-0">

            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Banco</th>
                    <th>Titular</th>
                    <th>Documento</th>
                    <th>Cuenta o teléfono</th>
                    <th>Tipo de cuenta</th>
                    <th>Estado</th>
                </tr>
            </thead>

            <tbody>

                @forelse ($datosBancarios as $datoBancario)
                    <tr>
                        <td>{{ $datoBancario->getTipo() }}</td>
                        <td>{{ $datoBancario->banco }}</td>
                        <td>
                            {{ $datoBancario->nombre_titular }}
                            <small class="d-block text-body-secondary">
                                {{ $datoBancario->getTipoTitular() }}
                            </small>
                        </td>
                        <td>{{ $datoBancario->numero_documento }}</td>
                        <td>{{ $datoBancario->numero_cuenta_telefono }}</td>
                        <td>{{ $datoBancario->getTipoCuenta() }}</td>
                        <td>
                            <x-list.status-badge :status="$datoBancario->estatus" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            La empresa no tiene datos bancarios registrados.
                        </td>
                    </tr>
                @endforelse

            </tbody>

        </table>

    </div>

</div>
