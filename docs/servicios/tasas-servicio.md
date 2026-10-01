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
3. Busca la tasa correspondiente al subtotal, salvo que exista exoneración o reprogramación.
4. Guarda en `servicio_json` el rango, valor y tipo usados.
5. Actualiza la tasa y el total del pasaje.

Finalmente suma precio base, descuentos, tasas y total en la reserva.

