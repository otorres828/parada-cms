# Empresa\ReembolsoService

## Objetivo

Gestiona reembolsos desde el ámbito de la empresa que recibió el pago. El panel administrativo de la plataforma los consulta y supervisa, pero no origina la operación comercial de la empresa.

## Flujo

`crear()` exige un pago asociado a una reserva pagada, un motivo válido y que no exista otro reembolso para el mismo pago. El registro comienza pendiente.

`revisar()` admite estas transiciones:

- Pendiente a aprobado o rechazado.
- Aprobado a pagado o rechazado.

Marcarlo pagado exige referencia y comprobante; se valida la unicidad de la referencia, no del archivo. Cambia la reserva a reembolsada y registra quién resolvió la operación. Las acciones quedan en auditoría.

## Alcance de la implementación actual

`crear()` copia `PagoReserva::total` como monto, incluida la tasa de servicio. Por tanto, la regla comercial de devolver únicamente el importe sin tasa todavía no está aplicada en este método. El servicio conserva los importes históricos de la reserva al marcarla reembolsada.

Aunque su namespace es `Empresa`, obtiene el actor desde `auth('admin')` y no verifica por sí mismo el alcance de la empresa del operador. El consumidor debe autorizar la operación; no debe asumirse que ya está adaptado al guard del futuro panel empresarial.

## Creación paso a paso

1. Valida existencia del pago y motivo de 10 a 2000 caracteres.
2. Bloquea el pago y exige su reserva pagada.
3. Rechaza un segundo reembolso del mismo pago.
4. Bloquea la empresa, copia el total del pago y registra pendiente y auditoría.

## Resolución paso a paso

1. Valida que la decisión esté permitida y bloquea el reembolso.
2. Comprueba la transición desde el estado actual.
3. Para pagado exige referencia y comprobante, rechaza referencia repetida y bloquea la reserva.
4. Exige reserva todavía pagada y la cambia a reembolsada.
5. Guarda comentario, revisor y fecha de resolución y audita.

No se conecta al banco: pagado registra una transferencia que el operador ya verificó.
