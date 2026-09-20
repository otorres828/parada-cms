# Checkout de reservas

El servicio `App\Services\ReservaService` coordina los pasos de compra. `TasasServicioService::calcularTasasReserva()` calcula exclusivamente las tasas y los totales de una reserva existente. El seeder recorre ReservaService para crear reservas, registrar pasajeros, aplicar cupones de ejemplo, preparar el resumen y pasar a pendiente/pagada o cancelar. El cálculo de tasas se ejecuta dentro de ese flujo.

## Secuencia de integración

El cliente debe estar autenticado al crear la reserva. Obtén `$cliente` de la sesión autenticada del sitio público, nunca de un ID enviado por el navegador. Todos los métodos de cliente comprueban su cuenta activa y la propiedad de la reserva.

```php
use App\Services\ReservaService;

// 1. Consulta orientativa. Devuelve los asientos elegibles y la cantidad disponible respetando el tope del trayecto.
$disponibilidad = ReservaService::consultarDisponibilidad($tarifaId);

// 2. Al continuar desde el itinerario, crea solo la reserva NUEVA, que vence en 20 minutos.
// Cotiza un boleto con tasa de servicio, sin crear pasajes, viajeros ni asignar asientos.
$reserva = ReservaService::aplicarReserva($cliente, $tarifaId);

// 3. Envía la lista completa actual de pasajeros y sus asientos.
// Repetir con más o menos pasajeros agrega o retira sus pasajes de la misma reserva.
$reserva = ReservaService::registrarPasajeros($cliente, $reserva->id, [
    [
        'numero_asiento' => 12,
        'nombre' => 'Ana',
        'apellido' => 'Pérez',
        'documento_identidad' => 'V12345678',
        'fecha_nacimiento' => '1990-06-15',
        'tipo_pasajero' => 'adulto',
    ],
    [
        'numero_asiento' => 13,
        'nombre' => 'Luis',
        'apellido' => 'Pérez',
        'documento_identidad' => 'V87654321',
        'fecha_nacimiento' => '1989-05-10',
        'tipo_pasajero' => 'adulto',
    ],
]);

// Para retirar a Luis, envía nuevamente la lista incluyendo únicamente a Ana.
// Una lista vacía retira todos los pasajes y recupera la cotización provisional de uno.

// 4. Opcional: aplica un cupón; null lo retira.
$reserva = ReservaService::aplicarCupon($cliente, $reserva->id, 'CODIGO');

// 5. Calcula tasas individuales y devuelve el resumen que se mostrará antes de pagar.
$reserva = ReservaService::prepararResumen($cliente, $reserva->id);

// 6. Al reportar el pago, congela los datos, elimina la expiración y cambia a PENDIENTE.
$reserva = ReservaService::pasarAPendiente($cliente, $reserva->id, 'tarjeta');
```

Envía al proveedor el importe y la moneda obtenidos en el servidor. Usa el código de reserva como referencia e idempotencia en la integración de pago. La transición a pendiente es idempotente para el mismo método y no crea ni ejecuta una operación externa.

El adaptador de pago deberá verificar la firma del aviso, consultar el cobro al proveedor, comprobar la referencia de reserva y evitar reutilizar la misma transacción externa. Solo entonces llamará desde el backend:

```php
ReservaService::confirmarPago($reservaId, $importeVerificado, 'USD');
```

Este método marca PAGADA y redime el cupón; no crea el registro de conciliación administrativa `pagos`. El módulo administrativo continúa registrando la conciliación y el movimiento de empresa. No expongas `confirmarPago` ni `marcarPagoFallido` como acciones accesibles directamente por el comprador.

El rechazo definitivo del administrador o del proveedor permite `marcarPagoFallido($reservaId)` y libera los asientos. Un timeout no demuestra que el pago falló: primero hay que consultar su resultado. `cancelarReserva($cliente, $reservaId)` cancela reservas nuevas; las transferencias pendientes requieren aprobación o rechazo administrativo.

## Reglas aplicadas

- Los 20 minutos aplican exclusivamente al estado NUEVO y empiezan al crear la reserva. Registrar pasajeros, abrir el resumen o la pantalla de pago no renueva ese plazo ni cambia el estado.
- Al reportar la transferencia, la reserva pasa a PENDIENTE y `fecha_expiracion` se guarda como `null`. Mantiene bloqueados sus asientos y cupón hasta aprobar o rechazar el pago, incluso después de los 20 minutos originales. Las reservas PAGADAS también bloquean sin expiración.
- Las NUEVAS vencidas dejan de bloquear automáticamente; no hace falta una tarea programada y su estado histórico no cambia. No pueden pasar a pendientes después del vencimiento: los asientos podrían haberse vendido nuevamente. Las PENDIENTES sí pueden aprobarse después del plazo original.
- Todos los boletos de una reserva comparten origen y destino. A→B y B→C pueden usar el mismo asiento; A→C se superpone con ambos.
- La reserva recién creada no ocupa asientos ni consume cupo: su monto es una cotización provisional de un boleto, con descuento cero y la tasa web estimada vigente. No crea registros ficticios de pasajeros o pasajes.
- `registrarPasajeros` recibe la lista completa actual, comprueba la disponibilidad excluyendo los propios pasajes y sincroniza altas y bajas en una transacción. Al retirar un pasajero libera su asiento; conserva los datos históricos del viajero. Los asientos se indican en este paso, no al crear la reserva.
- Al cambiar la lista se actualizan los importes, se reparte nuevamente el cupón si existe y se calculan las tasas por pasaje. Si queda vacía, se retira el cupón y vuelve la cotización unitaria; no se puede enviar el pago sin al menos un pasajero registrado. Ninguna de estas operaciones reinicia la expiración.
- Disponibles = máximo entre cero y (tope del tramo − asientos ocupados que se solapan con ese trayecto). Sin tope se usa la capacidad del autobús y la programación; nunca se supera esa capacidad. Por ejemplo: tope 10, ocupados 2, disponibles 8. No se guarda un contador global en la programación.
- La disponibilidad se calcula por origen y destino: tope efectivo menos asientos distintos ocupados en cualquier segmento del trayecto consultado. Un asiento vendido A→B y B→C se resta una sola vez al consultar A→C. Las reservas sin pasajes no ocupan asientos.
- `consultarDisponibilidadPorTramos($programaciones)` permite al panel consultar todas las tarifas de varias programaciones con una carga conjunta de reservas, incluyendo salidas históricas. Devuelve el tope efectivo, ocupados y disponibles por tarifa. La clave interna `cupo_tramo` conserva el mismo valor que `disponibles` para validar la compra. `asientos` contiene los números elegibles y la cantidad seleccionada no puede superar `disponibles`. El dashboard y el detalle usan este cálculo; la compra vuelve a comprobar los asientos bajo bloqueo transaccional.
- El precio sale de la tarifa persistida: al crear se envía su ID; al registrar pasajeros se envían sus datos y asientos. La cotización inicial es provisional: cada pasaje nuevo toma el precio vigente del servidor y los existentes conservan su precio base.
- La hora de embarque intermedia se estima sumando las duraciones de los tramos anteriores. Si faltan duraciones, se rechaza esa venta. No hay seguimiento en tiempo real de la ubicación del autobús.
- El cupón fijo se aplica una vez al total de la reserva; el porcentual se calcula sobre ese total. El descuento se reparte proporcionalmente entre boletos sin perder centavos y nunca supera su precio base.
- Un cupón no redimido queda reservado por una compra nueva vigente o pendiente de revisión; se redime únicamente al aprobar el pago. Una reserva cancelada, fallida o nueva vencida deja de bloquearlo.
- Cambiar o quitar el cupón de una reserva NUEVA invalida sus tasas para generar otro resumen. Reabrir el mismo resumen conserva los importes y rangos ya calculados.
- La tasa web es por boleto, sobre el precio final tras descuento. No incluye tasa de embarque. El sistema requiere una regla activa aplicable, incluso para tasa cero.
- Cada operación se ejecuta en una transacción. La programación se bloquea antes de comprobar inventario y registrar pasajeros. Las escrituras de otros canales deberán seguir el mismo protocolo para evitar sobreventa.
- Para cambiar pasajeros o asientos, sincroniza la lista en la misma reserva nueva. Para cambiar el trayecto, cancela y crea otra. Conserva el ID devuelto al crear: repetir la creación genera otra reserva vacía, no una asignación de asientos.

## Esquema y pruebas

La migración original de `pasajes` permite `viajero_id = null` durante la selección y evita repetir un asiento dentro de la misma reserva. Aplica el esquema al reconstruir la base; no se ejecutó ninguna migración sobre la base actual.

`tests/CheckoutFlowSmoke.php` ejecuta migraciones y pruebas en SQLite en memoria. Cubre el flujo, permisos de propiedad, solapamientos, caducidad, cupos, descuentos, tasas cero, reintentos y confirmaciones. SQLite no reproduce los bloqueos de filas de MySQL: la concurrencia real debe comprobarse también en el entorno de integración antes de conectar una pasarela.

Este proyecto todavía no incluye las pantallas del checkout público ni un adaptador de pasarela. Los métodos están preparados para que ese sitio los utilice.

## Datos de demostración

`AdminDemoSeeder` conserva 72 compras: 54 pagadas, 12 pendientes y 6 canceladas. Tres compras utilizan cupones, uno por empresa. Las reservas existentes se conservan al repetirlo. Las compras históricas se simulan con un reloj anterior a la salida, que se restaura siempre; la empresa inactiva de ejemplo se desactiva después de generar sus compras iniciales. No se reactiva una empresa existente para crear compras adicionales.

`tests/DemoCheckoutSeederSmoke.php` verifica ese recorrido en SQLite temporal, los importes de conciliación tras descuentos, el plazo inicial de 20 minutos, la eliminación de la expiración al reportar el pago y la repetición sin duplicados. Las confirmaciones del seeder son ficticias y no ejecutan cobros externos.
