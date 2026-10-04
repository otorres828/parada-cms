# SaveReserva: nueva reserva por taquilla

Ruta: `empresas.reservas.add`, disponible desde el listado y el botón Nueva Reserva del dashboard.

## Cotización en una pantalla

1. Seleccionar fecha y origen. Los destinos proceden de las tarifas de los tramos disponibles de la empresa.
2. Elegir destino y después una salida que cubra ese tramo. No es necesario conocer el recorrido completo del autobús.
3. Al seleccionar una salida se muestran el transporte y sus amenidades, y se habilitan los campos del comprador y pasajeros. Completar comprador y añadir pasajeros. No se crean usuarios ni viajeros; el contacto y los pasajeros serán snapshots cifrados.
4. El infante tiene un selector Alpine: sin asiento por defecto, o con asiento a precio normal. Sin asiento tiene precio, descuento y tasa cero, y no consume disponibilidad.
5. El resumen muestra precio, cantidad de pasajeros y asientos, subtotal, descuento, tasa cero, total y saldo por completar. En esta pantalla no se aplican descuentos manuales ni cupones.
6. Al agregar pasajeros con asiento se habilitan los pagos. Los inputs de bolívares y dólares se convierten entre sí al cambio vigente; se conserva la moneda del último input editado. Pago móvil exige cuenta receptora y referencia. Tarjeta exige referencia, sin cuenta receptora; efectivo no exige ninguno de esos datos. Se pueden retirar pasajeros o pagos antes del registro.
7. Tras recibir el dinero, pulsar Registrar. Se verifica nuevamente tarifa, disponibilidad por tramo, datos y suma de pagos. Se guarda la reserva completa o se revierte todo.

Antes de Registrar no existe reserva, pasaje ni pago en la base; tampoco hay puestos bloqueados. Abandonar la pantalla descarta la cotización. No es necesario un job de eliminación para este flujo. Esta pantalla solo crea reservas: no recibe un identificador para editar ni ofrece acciones de detalle. Después del registro limpia la cotización y renueva el token de la siguiente venta.

## Cobros y permisos

- `reservas/add`: cotizar y registrar.
- `reservas/confirm`: confirmar cualquier cobro de taquilla. La verificación posterior pertenece a su pantalla correspondiente.
- `reservas/list`: enlace al listado.

Métodos numéricos: transferencia 1 (conservada, oculta en este formulario), efectivo 2, pago móvil 3 y tarjeta 4. La confirmación de taquilla declara recibido el pago completo, también en pago móvil. Requiere reservas/confirm y deja la reserva pagada, sin expiración y con los QR disponibles.

Cada pago se almacena en dólares, sin columna moneda. El selector de cuenta muestra banco, cuenta/teléfono y tipo y número de documento del titular. El equivalente se calcula en la moneda base usando el cambio de la reserva. La suma debe coincidir exactamente a dos decimales con el total; si cambia tarifa o cambio durante la cotización, el vendedor debe revisar los importes antes de registrar.

La reserva se limita a la empresa autenticada. Un token Locked permite que un reintento del registro devuelva la misma venta. No se confía en precios, totales ni empresa enviados por el navegador.

## Componentes

`comprador`, `pasajero`, `resumen-taquilla`, `pagos-taquilla`, `transporte-taquilla`, `tramo-taquilla` y `pasajeros-cotizacion` viven en `components/empresas/reservas`. Formularios usan Alpine y validación PHP con mensajes en español.

## Esquema

Se modifican las migraciones originales: asiento nullable para infantes sin puesto; pagos de una reserva pasan a uno-a-muchos y conservan los importes en dólares. Actualizar el esquema de desarrollo y ejecutar GroupSectionPermissionEmpresaSeeder. No se ejecutó fresh sobre la base local.

## Horario del tramo

La fecha seleccionada corresponde al abordaje en el origen elegido, no al inicio del recorrido completo. Las opciones muestran la salida del tramo y la venta guardada muestra su llegada. Consultar [reglas de horarios](../../../servicios/logica-horarios-tramos.md).

## Estado y validación del navegador

Pasajeros y pagos tienen su propio Alpine.data, referencias de formulario e identificadores estables. Comparten datos con Livewire mediante entangle. Cada envío reconstruye su validador con los campos actuales para que los cambios de filtros y las actualizaciones de otro formulario no conserven referencias a elementos anteriores. Las instancias se destruyen después de validar correctamente o al salir de la pantalla.

Cambiar fecha, origen, destino o salida descarta los pagos cotizados para revisar los importes de la nueva selección; conserva los datos de pasajeros y comprador. Retirar todos los pasajeros también limpia los pagos. El servidor comprueba la salida y la empresa antes de añadir datos a la cotización, además de las validaciones completas al registrar.

El botón Registrar solo está disponible cuando hay pasajeros con asiento, pagos y saldo cero. La validación final de comprador y de importes también se hace en el servidor.

En escritorio, el panel derecho queda fijo durante el desplazamiento. El contenido tiene scroll interno si supera el alto disponible, mientras el botón Registrar permanece visible. En pantallas pequeñas vuelve al flujo normal para no cubrir los campos.

Antes de guardar aparece un modal con comprador, tramo, salida, transporte, pasajeros y totales. Volver a revisar no persiste nada. Los pagos cotizados muestran siempre USD y su equivalente en BS.

Al agregar o retirar pasajeros o pagos correctamente, el listado afectado y el resumen resaltan su borde durante tres segundos. Cada nuevo cambio reinicia el tiempo; retirar el último pasajero también resalta los pagos que se limpian. Las validaciones fallidas no activan el resaltado.

Los precios de los pasajeros y todos los importes del resumen de compra muestran dólares y su equivalente en bolívares, usando el tipo de cambio de la cotización. Los infantes sin asiento muestran cero en ambas monedas.
