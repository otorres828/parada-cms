# PagoReservaService

## Reporte del cliente

`pasarAPendiente()` recibe la cuenta bancaria, referencia, fecha y comprobante opcional. Valida propiedad, vigencia, cupón, método activo y unicidad de la referencia. Después calcula las tasas definitivas, crea `PagoReserva`, cambia la reserva a pendiente y elimina su expiración.

Repetir exactamente el mismo reporte sobre una reserva pendiente devuelve el estado existente. Una referencia o método diferente se rechaza.

## Validación administrativa

`confirmarPago()` exige una reserva pendiente con pago y que el importe confirmado coincida con el total registrado. Al aprobar, la reserva pasa a pagada y registra la fecha de pago. Si procede de una reprogramación, la reserva original pasa a reprogramada.

`marcarPagoFallido()` admite reservas pendientes o ya fallidas, cambia el estado y libera el cupón aplicado.

Las operaciones usan transacciones y bloqueo de la reserva para impedir transiciones simultáneas incompatibles.

