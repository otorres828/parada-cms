# Reservas y checkout

## Responsabilidad

Este CRM contiene los servicios de negocio; las pantallas del checkout pertenecen al proyecto del sitio de venta. Ese consumidor debe proteger el checkout con autenticación y pasar al cliente autorizado. Los servicios de reserva verifican la propiedad de la reserva y del viajero recibido.

Una reserva representa una compra para una programación y un tramo concreto. Todos sus pasajeros comparten origen y destino. Los asientos se asignan automáticamente al incorporar viajeros y su ocupación se calcula por los tramos que se superponen.

## Secuencia

1. `Pasaje::consultarDisponibilidad($tarifaId)` consulta el cupo del tramo.
2. `ReservaService::aplicarReserva($cliente, $tarifaId, $reprogramacionId)` crea una reserva nueva o reinicia una reserva nueva reutilizable. La cotización inicial representa un pasaje y vence a los 20 minutos.
3. El cliente crea o selecciona un viajero mediante `ViajeroService`.
4. `ReservaService::agregarPasajero($cliente, $reservaId, $viajeroId)` crea el pasaje, asigna el primer asiento disponible y guarda el snapshot cifrado del viajero.
5. `ReservaService::removerPasajero($cliente, $reservaId, $pasajeId)` elimina el pasaje mientras la reserva sea editable.
6. `CuponService::aplicarCupon()` o `removerCupon()` modifica el descuento. Agregar o retirar pasajeros recalcula el cupón existente.
7. `ReservaService::prepararResumen()` valida la reserva y calcula sus tasas.
8. `PagoReservaService::pasarAPendiente()` registra el pago reportado, elimina la expiración y deja la reserva esperando revisión.
9. El backend autorizado ejecuta `confirmarPago()` o `marcarPagoFallido()`.

## Estados y ocupación

- **Nueva:** bloquea cupo solamente mientras `fecha_expiracion` siga vigente.
- **Pendiente:** el cliente terminó el pago y espera validación; no tiene expiración.
- **Pagada:** pago confirmado y pasajes emitidos.
- **Cancelada o fallida:** no bloquea cupo.
- **Reprogramada:** identifica una compra pagada sustituida por otra reserva.
- **Reembolsada:** conserva el histórico del pago y de la tasa de servicio.

Los pasajes de reservas nuevas vigentes, pendientes y pagadas ocupan asientos. Una reserva sin pasajes solo mantiene una cotización y no ocupa cupo. La disponibilidad se determina por solapamiento entre origen y destino, por lo que un asiento puede venderse nuevamente después del terminal donde su pasajero desciende.

## Pasajeros y cotización

La reserva no crea viajeros. `agregarPasajero()` recibe un viajero activo ya existente y comprueba propiedad, duplicados y disponibilidad. Cada alta o retiro recalcula precio, descuento, tasa y total.

Si se retira el último pasaje, la reserva vuelve a la cotización provisional de una persona y libera el cupón. El viajero permanece en la libreta del cliente.

## Cupones

Una campaña define modalidad, tipo de descuento y ámbito de aplicación:

- **Reserva:** calcula un descuento general y lo distribuye entre los pasajes.
- **Pasajes:** aplica el descuento individualmente a cada pasaje.

El cupón se valida nuevamente antes de reportar el pago. Si una reserva nueva se cancela o expira, el código se libera cuando corresponde.

## Tasas de servicio

La tasa la paga el cliente por pasaje. `TasasServicioService` calcula cada tasa sobre el subtotal después del descuento y suma los resultados en la reserva.

No se genera tasa cuando existe una exoneración aplicable o cuando la reserva procede de una reprogramación. El pasaje conserva en `servicio_json` las condiciones usadas para que cambios futuros en la configuración no alteren el histórico.

## Pagos

Reportar un pago crea un único `PagoReserva`, cambia la reserva de nueva a pendiente y elimina su fecha de expiración. Una referencia no puede registrarse dos veces.

Confirmar exige que el importe recibido coincida con la reserva y el registro del pago. En una reprogramación, la confirmación marca la reserva original como reprogramada. Un pago fallido libera el cupón aplicado.

## Concurrencia

Las operaciones críticas se ejecutan dentro de transacciones. La reserva se bloquea durante sus modificaciones y la programación se bloquea cuando se asignan o liberan asientos, evitando que dos solicitudes consuman el último cupo del mismo tramo.

## Verificación

`tests/ReservaFlowSmoke.php` ejecuta el flujo en SQLite en memoria y verifica propiedad, disponibilidad, cupones, tasas, pagos, vencimiento, snapshots y eliminación de viajeros.
