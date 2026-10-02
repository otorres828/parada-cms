# ReservaService

Fuente: `app/Services/ReservaService.php`. Sus métodos públicos son estáticos y reciben al cliente de la compra. La autenticación corresponde al consumidor; el servicio comprueba la propiedad de las reservas que modifica.

## Crear o reiniciar

`aplicarReserva(User $cliente, int $tarifaId, ?int $reprogramacionId = null)` busca la reserva nueva más reciente del cliente para esa programación, sin pago, incluso si está vencida. Si existe, libera su cupón y elimina sus pasajes dentro de la transacción; si no existe, crea una reserva.

Consulta disponibilidad, exige un tipo de cambio vigente y registra el tramo, tarifa, exoneración, cotización provisional de un pasaje y expiración de 20 minutos. No crea pasajes ni ocupa asientos. Reiniciar conserva el ID y reutiliza la referencia si su formato corresponde al transporte.

Una reprogramación requiere una reserva original pagada del mismo cliente y sin otra reprogramación activa. El servicio no establece restricciones adicionales de empresa, recorrido o cantidad de viajeros.

## Pasajeros

`agregarPasajero(User $cliente, int $reservaId, int $viajeroId)` exige un viajero activo del cliente, evita duplicarlo en esa reserva, consulta disponibilidad por tramo, asigna el primer asiento libre y crea su snapshot cifrado. Recalcula cupón y tasas.

`removerPasajero(User $cliente, int $reservaId, int $pasajeId)` elimina el pasaje de una reserva editable. Si quedan pasajes, recalcula los importes; si no quedan, libera el cupón y restablece la cotización provisional. No elimina el viajero ni renueva la expiración.

## Resumen y cancelación

`prepararResumen()` valida que la reserva pueda editarse y que su cupón siga asociado correctamente; calcula tasas y devuelve el detalle. Requiere al menos un pasaje.

`cancelarReserva()` admite estados nuevo o cancelado, elimina la expiración y libera el cupón. No cancela reservas pendientes o pagadas.

## Transacciones

`conReserva()` filtra por cliente y bloquea la reserva antes de ejecutar la modificación. Agregar pasajeros bloquea además el viajero y la programación; retirar bloquea la programación. `aplicarReserva()` utiliza una transacción pero su búsqueda de reserva reutilizable no emplea `lockForUpdate()`: no debe describirse como una garantía de creación única frente a solicitudes simultáneas.

El flujo completo entre servicios está en [Lógica del checkout](logica-checkout.md).
