# PagoReservaService

## Reporte del cliente

`pasarAPendiente()` recibe la cuenta bancaria, referencia, fecha y comprobante opcional. Valida propiedad, vigencia, cupón, método activo y unicidad de la referencia. Después calcula las tasas definitivas, crea `PagoReserva`, cambia la reserva a pendiente y elimina su expiración.

Repetir el método y la referencia sobre una reserva pendiente devuelve el estado existente. No compara ni actualiza la fecha o el comprobante en ese reintento. Una referencia o método diferente se rechaza.

## Validación administrativa

`confirmarPago()` exige una reserva pendiente con pago y que el importe confirmado coincida con el total registrado. Al aprobar, la reserva pasa a pagada y registra la fecha de pago. Si procede de una reprogramación, la reserva original pasa a reprogramada.

`marcarPagoFallido()` admite reservas pendientes o ya fallidas, cambia el estado y libera el cupón aplicado.

Las operaciones usan transacciones y bloqueo de la reserva para impedir transiciones simultáneas incompatibles.

Una confirmación repetida sobre una reserva ya pagada devuelve el detalle si sus importes coinciden. Confirmación y rechazo requieren autorización del backend llamador. El método bancario se valida como activo, pero el servicio todavía no restringe su titularidad según la empresa y el tipo de contrato.
