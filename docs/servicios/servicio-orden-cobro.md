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
