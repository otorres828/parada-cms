# SaveReserva: nueva reserva por taquilla

Ruta: `empresas.reservas.add`, disponible desde el listado y el botón Nueva Reserva del dashboard.

## Cotización en una pantalla

1. Seleccionar fecha y origen. Los destinos proceden de las tarifas de los tramos disponibles de la empresa.
2. Elegir destino y después una salida que cubra ese tramo. No es necesario conocer el recorrido completo del autobús.
3. Completar comprador y añadir pasajeros. No se crean usuarios ni viajeros; el contacto y los pasajeros serán snapshots cifrados.
4. El infante tiene un selector Alpine: sin asiento por defecto, o con asiento a precio normal. Sin asiento tiene precio, descuento y tasa cero, y no consume disponibilidad.
5. El resumen muestra precio, cantidad de pasajeros y asientos, subtotal, descuento, tasa cero, total y saldo por completar. En esta pantalla no se aplican descuentos manuales ni cupones.
6. Añadir uno o varios pagos indicando método, moneda, monto, cuenta y referencia cuando corresponda. Se puede retirar un pasajero o pago antes del registro.
7. Tras recibir el dinero, pulsar Registrar. Se verifica nuevamente tarifa, disponibilidad por tramo, datos y suma de pagos. Se guarda la reserva completa o se revierte todo.

Antes de Registrar no existe reserva, pasaje ni pago en la base; tampoco hay puestos bloqueados. Abandonar la pantalla descarta la cotización. No es necesario un job de eliminación para este flujo. El identificador opcional de la ruta permite consultar ventas ya registradas y gestionar sus cobros.

## Cobros y permisos

- `reservas/add`: cotizar, registrar y enviar pasajes pagados.
- `reservas/confirm`: registrar efectivo o tarjeta como recibidos y verificar/rechazar los pagos pendientes.
- `reservas/list`: enlace al listado.

Métodos numéricos: transferencia 1 (conservada, oculta en este formulario), efectivo 2, pago móvil 3 y tarjeta 4. Si se incluye pago móvil, la reserva queda pendiente, sin expiración, hasta verificar el cobro. Solo efectivo/tarjeta queda pagada. El QR se muestra después de aprobar el pago.

Cada pago conserva moneda y monto recibido. El equivalente se calcula en la moneda base usando el cambio de la reserva. La suma debe coincidir exactamente a dos decimales con el total; si cambia tarifa o cambio durante la cotización, el vendedor debe revisar los importes antes de registrar.

La reserva se limita a la empresa autenticada. Un token Locked permite que un reintento del registro devuelva la misma venta. No se confía en precios, totales ni empresa enviados por el navegador.

## Componentes

`comprador`, `pasajero`, `resumen-taquilla`, `pagos-taquilla` y `pasajes-taquilla` viven en `components/empresas/reservas`. Formularios usan Alpine y validación PHP con mensajes en español.

## Esquema

Se modifican las migraciones originales: asiento nullable para infantes sin puesto; pagos de una reserva pasan a uno-a-muchos y conservan monto recibido y moneda. Actualizar el esquema de desarrollo y ejecutar GroupSectionPermissionEmpresaSeeder. No se ejecutó fresh sobre la base local.

## Horario del tramo

La fecha seleccionada corresponde al abordaje en el origen elegido, no al inicio del recorrido completo. Las opciones muestran la salida del tramo y la venta guardada muestra su llegada. Consultar [reglas de horarios](../../../servicios/logica-horarios-tramos.md).
