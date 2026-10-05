# ReprogramacionService (Empresa)

## Requisitos

La reserva debe tener `reprogramacion_id` nulo: una reserva que proviene de una reprogramación no puede reprogramarse otra vez. Se excluye de la búsqueda y se rechaza al validar o confirmar. La reserva debe estar pagada, todos sus pasajes sin abordar (abordado falso y hora_abordaje nula) y sin reprogramación activa. Se permite reprogramar antes de la fecha del viaje. Si esa fecha ya pasó, se concede todo el día siguiente: el plazo vence al final del día posterior a la fecha original. La hora de salida no interviene en este límite; se usa la fecha del tramo original.

Se conserva el origen, destino, comprador, pasajeros y sus snapshots cifrados, los descuentos y el origen de venta. La salida nueva debe ser diferente, activa y pertenecer a la misma empresa. Permite salidas de hoy aunque haya pasado la hora, conforme a la regla de taquilla. Debe tener puestos para todos los pasajeros que ocupaban asiento; los infantes sin asiento siguen sin asiento ni precio.

## Importes y pagos

El subtotal original es monto_pasajes menos descuento_aplicado. Se aplica el precio nuevo a cada pasajero con asiento y se conservan los descuentos individuales. El nuevo subtotal no puede ser inferior al original. Tasa de servicio cero. Solo se crean pagos_reserva por la diferencia, convertidos y guardados en USD; se validan cuentas de la empresa, referencias y suma exacta.

Los pagos anteriores no se copian ni se mueven: permanecen en la reserva original, accesible mediante reprogramacion_id. Así no se duplica el dinero recibido. No se vuelve a consumir el cupón: se conserva el descuento económico, pero la nueva reserva no vuelve a vincular la redención.

## Transacción

1. Valida permiso y empresa; bloquea programaciones en orden de ID y reserva original.
2. Si ya existe la nueva reserva con el mismo código, devuelve esa reserva (reintento).
3. Revalida estado, plazo, pasajeros, tramo, tarifa, disponibilidad y subtotal.
4. Crea la nueva reserva pagada con reprogramacion_id apuntando a la original; genera nuevos pasajes y localizadores.
5. Registra solo los pagos de diferencia y exige la suma exacta.
6. Marca la original ESTADO_PAGO_REPROGRAMADO, liberando sus puestos y deshabilitando sus QR. Los nuevos QR están disponibles por el estado pagado.

Cualquier error revierte todos los cambios. No existe edición ni eliminación de reprogramaciones.
