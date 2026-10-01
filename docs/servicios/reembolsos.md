# Empresa\ReembolsoService

## Objetivo

Gestiona reembolsos desde el ámbito de la empresa que recibió el pago. El panel administrativo de la plataforma los consulta y supervisa, pero no origina la operación comercial de la empresa.

## Flujo

`crear()` exige un pago asociado a una reserva pagada, un motivo válido y que no exista otro reembolso para el mismo pago. El registro comienza pendiente.

`revisar()` admite estas transiciones:

- Pendiente a aprobado o rechazado.
- Aprobado a pagado o rechazado.

Marcarlo pagado exige referencia y comprobante únicos, cambia la reserva a reembolsada y registra quién resolvió la operación. Las acciones quedan en auditoría.
