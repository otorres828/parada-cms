# OrdenCobroService

## Emisión

Las órdenes corresponden a empresas con contrato `CONTRATO_ELLOS_RECIBEN`. `generar()` evita dos órdenes abiertas para la misma empresa, determina el período posterior a la última orden y suma las tasas de reservas pagadas, reprogramadas o reembolsadas con tasa mayor que cero.

La orden conserva un snapshot de las reservas incluidas, cantidad, total, período, fecha de emisión y vencimiento. Su código definitivo utiliza el formato `OC-AÑO-ID`.

## Ciclo

1. **Emitida:** la empresa todavía no ha reportado su transferencia.
2. **Pendiente:** la empresa indicó referencia y comprobante.
3. **Rechazada:** administración rechazó el reporte y agregó el motivo; la empresa puede reportar nuevamente.
4. **Aprobada:** administración confirmó la transferencia y la orden quedó cerrada.

## Bloqueo de empresa

Una orden vencida emitida o rechazada bloquea a la empresa. Una orden pendiente solo la bloquea si el pago fue reportado después del vencimiento. Un pago legítimamente reportado antes del límite permite que administración lo revise después sin penalizar a la empresa.

Al aprobar y no quedar deudas vencidas, se despacha la reactivación automática.

El bloqueo se almacena en `empresas.bloqueada_por_cobranza_at`; estos métodos no cambian directamente `estatus`. Reactivar elimina esa marca, sin reemplazar la decisión administrativa de activar o inactivar la empresa.

El período se calcula por `reservas.fecha_pago`. La primera orden toma siete días; las siguientes empiezan un segundo después del final de la última. Si no hay reservas cobrables, no se genera una orden. La emisión puede omitir notificaciones mediante `$notificar = false`.

## Emisión paso a paso

1. Bloquea la empresa y comprueba contrato y día de corte.
2. Si hay alguna orden distinta de aprobada, devuelve null.
3. Calcula periodo_hasta como la fecha de corte a la hora configurada menos un segundo; si ya se cubrió ese período, no repite.
4. Define periodo_desde desde la última orden o siete días atrás para la primera.
5. Consulta reservas cobrables por fecha_pago y empresa; si no hay resultados devuelve null.
6. Crea el snapshot, suma las tasas y genera el código OC con el ID rellenado a seis dígitos.
7. Calcula el próximo día de vencimiento desde la emisión real. Si coincide con el día actual, utiliza la semana siguiente.
8. Despacha la notificación de emisión después del commit cuando notificar es true.

## Reporte y resolución paso a paso

1. reportarPago recibe órdenes emitidas o rechazadas y registra referencia, comprobante y hora actual del reporte.
2. Cambia a pendiente y añade comentario solo si se proporcionó.
3. rechazar exige pendiente, agrega motivo y envía la notificación después del commit.
4. aprobar exige pendiente, registra actor y fecha y despacha la reactivación.

El llamador debe comprobar que la empresa puede actuar sobre ese ID: este servicio no recibe al usuario empresarial para comprobar propiedad.
