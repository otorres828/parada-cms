# Horarios por tramo de programación

Los tramos de la ruta (`viaje_tramos`) son todas las combinaciones vendibles de la plantilla, con precio independiente, duración y orden explícito. Las fechas concretas pertenecen a `programacion_tramo_precios`, porque cada programación ocurre en un día y horario distinto.

Cada tramo comercial guarda:

- fecha_salida y hora_salida: salida desde su terminal de origen.
- fecha_llegada y hora_llegada: llegada a su terminal de destino.

En A → B → C, un boleto B → C muestra la salida desde B y la llegada a C. El último punto siempre se presenta como llegada, sin guardar una llegada en un campo llamado salida. Los campos de fecha permiten llegar o abordar al día siguiente.

## Regla de diseño: terminales propios de cada programación

`viaje_tramos` y `programacion_tramo_precios` conservan ambos `origen_terminal_id` y `destino_terminal_id`. Esta repetición es intencional y no debe eliminarse como una simple normalización:

- `viaje_tramos` define todas las combinaciones A → B, A → C, B → C. `orden` numera las combinaciones generadas por los dos bucles. Las que parten del origen principal, ordenadas por ese campo, reconstruyen el recorrido: A→B, A→C, A→D permiten recuperar [A,B,C,D]. No se guardan posiciones adicionales.
- `programacion_tramo_precios` define los trayectos comerciales de una salida concreta, con sus terminales, precio y horarios propios. Puede incluir A → C, copiando la combinación de la plantilla. Solo deben crearse las tarifas seleccionadas al generar una programación; la plantilla conserva todas las combinaciones.
- Los terminales de la tarifa conservan los extremos definidos para esa programación. No deben resolverse dinámicamente desde un `viaje_tramo_id` mutable: cambiar el origen o destino del tramo referenciado podría alterar la interpretación de ventas existentes.

No sustituir esos campos por `viaje_tramo_id` sin rediseñar y verificar el tratamiento de trayectos compuestos y del historial. Tampoco propagar automáticamente cambios de terminales o precios base de una ruta a tarifas que ya tienen reservas, ni eliminar estas tarifas en cascada al editar el recorrido.

### Alcance de la protección y regla para futuras ediciones

Conservar los terminales en la tarifa no congela todo el recorrido. Actualmente `Terminal::obtenerSecuenciaRuta()` utiliza `viaje_tramos` para calcular los intervalos y la disponibilidad; además, el inicio y fin general de la programación dependen de los extremos de `viajes`. Alterar las paradas, su orden o esos extremos puede afectar programaciones existentes aunque sus tarifas mantengan los terminales.

Al implementar la edición o eliminación de rutas y tramos, debe impedirse modificar el recorrido utilizado por programaciones con reservas o conservarse explícitamente su versión histórica. Para un recorrido diferente, crear una nueva ruta es una alternativa que preserva el anterior. `SaveViaje` y `ViajeService` ya impiden cambiar origen/destino en edición y bloquean cambios de paradas cuando existe cualquier programación. Los precios base sí se pueden editar, sin actualizar las tarifas existentes. Escrituras directas fuera de ese flujo deben respetar la misma regla.

## Consulta y registro

`ProgramacionTramoPrecio::paraTaquilla` filtra por empresa, programación/ruta/transporte activos y fecha de salida del tramo. No excluye orígenes por la hora. Si el autobús inició ayer pero pasa por B hoy, B aparece al consultar hoy.

`ProgramacionTramoPrecio::getSalida()` y `getLlegada()` devuelven exclusivamente los horarios del tramo. No existe respaldo en la programación: sin horarios completos de salida y llegada, la venta se rechaza. Los campos pueden estar vacíos mientras se configura el tramo.

En taquilla, `validarSalida(validarHora: false)` permite vender durante toda la fecha del tramo, aunque su hora de salida haya pasado. También permite ventas anticipadas; rechaza fechas anteriores al día actual. La programación, ruta y transporte deben seguir activos. El formulario comprueba que el tramo corresponda a la fecha seleccionada. No existe apertura ni cierre manual de embarque.

Los servicios de reserva/pago web mantienen `validarSalida()` con el horario futuro como requisito. La confirmación administrativa posterior de un pago pendiente no utiliza este corte de venta.

Las fechas/horas se guardan juntas; la llegada debe ser posterior a la salida. Para varios tramos con el mismo origen debe asignarse la misma salida, y para el mismo destino la misma llegada. El seeder calcula una línea de tiempo por terminal a partir de la duración de cada tramo y copia sus extremos a cada tarifa O&D.

## Presentación y datos de desarrollo

Taquilla, correo, detalle del pasaje, matriz de tarifas y exportaciones de reservas y pasajes usan el horario del tramo comprado.

La tabla `programaciones` ya no contiene `fecha_salida` ni `hora_salida`. `Programacion::getSalida()` obtiene el primer horario de los trayectos habilitados para vender; `getLlegada()` obtiene el último horario de llegada de esos trayectos. Los O&D con un mismo origen deben compartir horario. Si falta el horario se presenta vacío, sin inventar una fecha.

Los filtros de fechas y próximas programaciones consultan la primera salida comercial habilitada mediante subconsultas. `salida_fecha` y `salida_hora` son alias calculados para ordenar los listados; no son columnas persistidas. Una programación que comenzó ayer puede vender hoy un tramo intermedio: taquilla filtra directamente la fecha de ese tramo.

Se modificaron las migraciones originales de programaciones y programacion_tramo_precios. El esquema y los datos existentes necesitan actualizarse antes de utilizar la consulta nueva. El seeder actualizado genera horarios al recrear los datos; no modifica programaciones existentes ni se ejecutó fresh sobre la base local. No hay actualmente formulario de alta/edición de programaciones; estos campos quedan listos para ese módulo.

Prueba: tests/HorariosTramosSmoke.php, con SQLite en memoria; cubre medianoche, origen intermedio, venta en taquilla después de la hora con pago y QR, rechazo de fechas pasadas y programaciones inactivas/finalizadas, corte horario web y aislamiento de empresas.

## Precios base de la ruta

`viaje_tramos.precio` es decimal de dos posiciones y nullable: null significa sin configurar, no un pasaje gratuito. `ViajeTramo::precioBase($viaje, $origenId, $destinoId)` busca exactamente esa combinación. No suma precios: A → C puede valer 12 aunque A → B cueste 10 y B → C cueste 5. Rechaza trayectos inexistentes o sin precio válido. El formulario exige completar el precio de todas las combinaciones.

`programacion_tramo_precios.precio` se conserva como precio propio de la salida. El seeder de rutas configura las bases y el seeder histórico copia el precio independiente de cada combinación en su tarifa comercial. Cambiar la base no modifica las tarifas ya creadas. El detalle de rutas muestra el precio base por segmento.

No existe todavía un formulario de alta de programaciones: cuando se implemente, utilizará este cálculo para sugerir/copiar el precio y permitirá ajustarlo para esa salida. Se modifica la migración original; no se ejecuta fresh ni se alteran registros locales al implementar esta regla.
