# Flujo de reservas: contrato y auditoría

## Identidad y autorización

Los servicios reciben al cliente de la compra, no al operador autenticado. No consultan la sesión ni agregan guards:

- Sitio final: middleware de autenticación en todo el checkout; el consumidor pasa `auth()->user()`, nunca un cliente indicado por el navegador.
- CRM: el administrador autenticado selecciona un `User` y lo pasa al servicio. El consumidor comprueba sus permisos y el alcance de empresa antes de ejecutar la operación.
- Las operaciones de ReservaService y el reporte de pago conservan el filtro `usuario_id`: la reserva debe pertenecer al cliente recibido. Autenticación y propiedad son controles distintos.
- CuponService recibe una reserva: el consumidor debe obtenerla desde las reservas del cliente o desde el ámbito autorizado del CRM. No debe usar un ID libre del navegador.
- Confirmación y rechazo de pagos son operaciones administrativas/backend. No se exponen como acciones del comprador.

## Secuencia vigente

1. `Pasaje::consultarDisponibilidad($tarifaId)` consulta cupo por trayecto.
2. `ReservaService::aplicarReserva($cliente, $tarifaId, $reprogramacionId)` crea una reserva NUEVA, con cotización provisional de un boleto y vencimiento de 20 minutos. No ocupa asientos todavía. El tercer argumento es opcional.
3. `ReservaService::agregarPasajero($cliente, $reservaId, $datos)` asigna automáticamente el primer asiento libre. `removerPasajero($cliente, $reservaId, $pasajeId)` libera ese asiento. Ambas recalculan descuentos y tasas; retirar el último recupera la cotización provisional.
4. Opcionalmente, `CuponService::aplicarCupon($reserva, $codigo)` o `removerCupon($reserva)`. Son métodos de instancia. Después se prepara el resumen para obtener las tasas actualizadas.
5. `ReservaService::prepararResumen($cliente, $reservaId)` valida pasajeros y cupón, calcula tasas y devuelve el detalle.
6. `PagoReservaService::pasarAPendiente($cliente, $reservaId, $metodoPagoId, $referenciaPago, $fechaPago, $comprobante)` registra el pago reportado. El comprobante es opcional. Revalida salida y vencimiento bajo bloqueo de programación; elimina la expiración. Repetir con el mismo método y referencia no crea otro pago.
7. Backend autorizado: `PagoReservaService::confirmarPago($reservaId, $montoConfirmado, 'USD')` después de verificar el pago, o `marcarPagoFallido($reservaId)` ante rechazo definitivo. Una confirmación repetida conserva el resultado.
8. `ReservaService::cancelarReserva($cliente, $reservaId)` admite nuevas y canceladas, nunca pendientes o pagadas.

Los pasajes de reservas nuevas vigentes, pendientes y pagadas bloquean asientos según el solapamiento del trayecto. Agregar pasajeros y reportar pagos no deben renovar el plazo. La tarea `reservas:cancelar-expiradas` libera los cupones de reservas vencidas; los asientos dejan de bloquear por la consulta de vigencia incluso antes de ejecutar la tarea.

## Simplificación aplicada

- Eliminado `ReservaService::validarCuponReserva`: solo delegaba a CuponService.
- Eliminado `CuponService::validarExistenciaYDisponibilidad`: sin consumidores en el repositorio; aplicarCupon ya valida dentro de su transacción.
- Eliminados el filtrado duplicado de datos ya validados del pasajero y la generación de localizador repetida; Pasaje ya genera su UUID.
- Eliminada la validación duplicada del intervalo al crear; disponibilidad ya la ejecuta.
- Eliminada la consulta `exists` duplicada del método bancario y comprobaciones de vigencia redundantes en pagos.
- Simplificado recalcularCupon para evitar la transacción y recargas adicionales de removerCupon.
- ReembolsoService ya no libera el cupón ni recalcula la reserva pagada: conserva los importes y tasas históricos usados por OrdenCobroService.

Se conservan conReserva, el recálculo compartido, la cotización inicial, las validaciones de estado, transacciones y bloqueos porque cumplen funciones distintas. TasasServicioService mantiene el snapshot de tasas para no cambiar importes al reabrir el resumen. OrdenCobroService mantiene su ciclo de emisión, reporte, revisión y suspensión; no se eliminaron sus métodos operativos.

## Hallazgos pendientes de reglas o revisión adicional

- PagoReservaService acepta cualquier cuenta bancaria activa. Falta definir la selección de cuenta según empresa y tipo de contrato (plataforma o empresa receptora) antes de restringirla.
- ReembolsoService solicita el total del pago, incluida la tasa. Confirmar si la política exige descontar la tasa no reembolsable antes de cambiar ese importe.
- CuponService usa `redimido` desde la aplicación, antes del pago; depende de la tarea de expiración para recuperar usos. La documentación anterior afirmaba otra cosa.
- El reparto proporcional del cupón usa floats y asigna el residuo al último boleto; requiere revisar los casos de centavos con precios pequeños o heterogéneos.
- Las reprogramaciones no restringen aquí empresa, trayecto ni cantidad de pasajeros. Esas reglas comerciales no se inventaron durante esta simplificación.
- La prueba en SQLite no demuestra el comportamiento concurrente de los bloqueos de MySQL. No se conectó una pasarela ni se implementaron pantallas del checkout.

## Verificación

`tests/ReservaFlowSmoke.php` ejecuta las migraciones exclusivamente en SQLite en memoria. Comprueba cliente seleccionado sin dependencia de sesión, propiedad, cupo, recálculo del cupón al agregar/retirar pasajeros, cotización vacía, pago repetido, importe incorrecto, cancelación de pagadas, salida inactiva, expiración y conservación del histórico al reembolsar.

Ejecutar con PHP 8.3+, BCMath y PDO SQLite: `php tests/ReservaFlowSmoke.php`.

## Bloqueo único de reserva

`conReserva` adquiere el bloqueo de la reserva que entrega a su callback. El recálculo y la liberación interna de cupones usan esa misma instancia y transacción, sin volver a bloquearla. La tarea de expiración también bloquea antes de liberar el cupón.

`aplicarCupon` y `removerCupon` conservan su bloqueo para llamadas independientes. Su argumento interno `reservaBloqueada: true` se usa exclusivamente cuando el llamador ya mantiene bloqueada esa reserva en una transacción; nunca procede de una petición del navegador. No se infiere que exista un bloqueo solo porque haya una transacción abierta.

Los bloqueos de programación, cupón, campaña y reserva original de una reprogramación protegen registros diferentes y se conservan.
