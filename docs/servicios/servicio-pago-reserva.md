# PagoReservaService

## Reporte del cliente

`pasarAPendiente()` recibe la cuenta bancaria, referencia, fecha y comprobante opcional. Valida propiedad, vigencia, cupón, método activo y unicidad de la referencia. Después calcula las tasas definitivas, crea `PagoReserva`, cambia la reserva a pendiente y elimina su expiración.

Repetir el método y la referencia sobre una reserva pendiente devuelve el estado existente. No compara ni actualiza la fecha o el comprobante en ese reintento. Una referencia o método diferente se rechaza.

## Validación administrativa

`confirmarPago()` exige una reserva pendiente con pago y que el importe confirmado coincida con el total registrado. Al aprobar, la reserva pasa a pagada y registra la fecha de pago. Si procede de una reprogramación, la reserva original pasa a reprogramada.

`marcarPagoFallido()` admite reservas pendientes o ya fallidas, cambia el estado y libera el cupón aplicado.

Las operaciones usan transacciones y bloqueo de la reserva para impedir transiciones simultáneas incompatibles.

Una confirmación repetida sobre una reserva ya pagada devuelve el detalle si sus importes coinciden. Confirmación y rechazo requieren autorización del backend llamador. El método bancario se valida como activo, pero el servicio todavía no restringe su titularidad según la empresa y el tipo de contrato.

## Secuencia del reporte

1. Valida formato de método, referencia, fecha no futura y comprobante opcional.
2. Bloquea la reserva del cliente recibido.
3. Si ya está pendiente, comprueba método y referencia y devuelve el resultado sin duplicar.
4. Exige método activo y reserva editable, bloquea la programación y vuelve a comprobar vigencia.
5. Valida el cupón y rechaza una referencia usada.
6. Calcula tasas, cambia a pendiente sin expiración y crea PagoReserva con los totales del servidor.

## Confirmación paso a paso

1. Bloquea la reserva y exige un pago registrado.
2. Compara el monto confirmado tanto con la reserva como con el pago.
3. Si ya está pagada devuelve el detalle; si no, exige pendiente.
4. Valida el cupón y registra estado pagado y fecha actual.
5. Si hay reprogramación, bloquea la original y exige que siga pagada antes de marcarla reprogramada.
6. Un error revierte todos los cambios de la transacción.

El reporte del cliente no equivale a una comprobación bancaria automática.
