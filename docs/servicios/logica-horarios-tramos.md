# Horarios por tramo de programación

Los tramos de la ruta (`viaje_tramos`) describen el recorrido reutilizable y su duración. Las fechas concretas pertenecen a `programacion_tramo_precios`, porque cada programación ocurre en un día y horario distinto.

Cada tramo comercial guarda:

- fecha_salida y hora_salida: salida desde su terminal de origen.
- fecha_llegada y hora_llegada: llegada a su terminal de destino.

En A → B → C, un boleto B → C muestra la salida desde B y la llegada a C. El último punto siempre se presenta como llegada, sin guardar una llegada en un campo llamado salida. Los campos de fecha permiten llegar o abordar al día siguiente.

## Consulta y registro

`ProgramacionTramoPrecio::paraTaquilla` filtra por empresa, programación/ruta/transporte activos y fecha de salida del tramo. No excluye orígenes por la hora. Si el autobús inició ayer pero pasa por B hoy, B aparece al consultar hoy.

`getSalida` y `getLlegada` devuelven los horarios del tramo. Para compatibilidad, sin horario explícito solo se admite la salida de la programación cuando el tramo comienza en el origen general. Nunca se reutiliza esa hora para una parada intermedia; debe configurarse su horario.

Al registrar, `validarSalida` exige un horario futuro en el punto de abordaje. La venta desde B sigue permitida si A ya salió pero B aún no. Se aplica en taquilla y en los servicios de reserva/pago web. La confirmación administrativa posterior de un pago pendiente no utiliza este corte de venta.

Las fechas/horas se guardan juntas; la llegada debe ser posterior a la salida. Para varios tramos con el mismo origen debe asignarse la misma salida, y para el mismo destino la misma llegada. El seeder calcula una línea de tiempo por terminal a partir de la duración de cada tramo y copia sus extremos a cada tarifa O&D.

## Presentación y datos de desarrollo

Taquilla, correo, detalle del pasaje, matriz de tarifas y exportaciones de pasajes usan el horario del tramo. No se cambió la hora general que describe el inicio del recorrido.

Se modificó la migración original de programacion_tramo_precios. El esquema y los datos existentes necesitan actualizarse antes de utilizar la consulta nueva. El seeder actualizado genera horarios al recrear los datos; no modifica programaciones existentes ni se ejecutó fresh sobre la base local. No hay actualmente formulario de alta/edición de programaciones; estos campos quedan listos para ese módulo.

Prueba: tests/HorariosTramosSmoke.php, con SQLite en memoria; cubre medianoche, venta desde parada intermedia, fecha de llegada, corte horario y aislamiento de empresas.
