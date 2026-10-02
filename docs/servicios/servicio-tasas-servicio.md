# TasasServicioService

## Objetivo

Determina si una compra está exonerada y calcula la tasa de servicio que paga el cliente por cada pasaje.

## Exoneraciones

`obtenerExoneracionTasa()` devuelve el snapshot de la exoneración vigente para la empresa. Las reprogramaciones se consideran exoneradas porque no deben cobrar una segunda tasa de servicio.

El snapshot se guarda en la reserva para conservar la causa histórica de una tasa igual a cero aunque posteriormente cambie la configuración.

## Cálculo

`calcularTasasReserva()` solamente acepta reservas nuevas sin pago y con al menos un pasaje. Para cada pasaje:

1. Valida precio y descuento.
2. Calcula el subtotal.
3. Usa cero si existe exoneración o reprogramación. Si ya hay `servicio_json`, conserva la tasa calculada; en otro caso busca la tasa correspondiente al subtotal.
4. Guarda en `servicio_json` el rango, valor y tipo usados.
5. Actualiza la tasa y el total del pasaje.

Finalmente suma precio base, descuentos, tasas y total en la reserva.

## Ejemplo del cálculo acumulado

Con dos pasajes de precio base 30, descuento individual 1 y tasa fija 1, cada pasaje conserva subtotal 29 y total 30. La reserva guarda monto_pasajes 60, descuento_aplicado 2, tasa_servicio 2 y monto_total 60.

Con la misma compra exonerada, las tasas de ambos pasajes son cero y monto_total es 58. La causa de exoneración queda en la reserva y servicio_json de esos pasajes se limpia.

Los cálculos monetarios usan BCMath a dos decimales. La transacción y el bloqueo de la reserva corresponden al servicio llamador.
