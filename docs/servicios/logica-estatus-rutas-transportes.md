# Estatus de rutas, transportes y programaciones

## Regla de negocio

Desactivar una ruta o un transporte NO desactiva, cancela ni inhibe las programaciones que ya los tienen relacionados. Tampoco bloquea la venta de sus trayectos por ese motivo ni altera reservas, horarios, precios o cupos existentes.

El estatus de ruta y transporte controla su disponibilidad como opción para nuevos registros. Si están inactivos, no aparecen como opciones al crear nuevas programaciones y el servidor rechaza su selección manual en una nueva alta.

La programación tiene estatus propio: si permanece programada puede vender conforme a sus fechas, disponibilidad y reglas de reserva. Para suspender una salida concreta se debe inactivar esa programación. La empresa sigue teniendo que estar activa y el usuario debe tener permiso; la inactivación de la empresa no está incluida en esta excepción.

Ejemplo: una ruta y un autobús tienen salidas creadas para los próximos diez días. Desactivar cualquiera los retira de los selectores de nuevas programaciones; las diez salidas existentes continúan operando. No se realiza una actualización en cascada.

## Implementación

Programacion::paraTaquilla y ReservaTaquillaService::validarSalida no exigen que ruta y transporte sigan activos. Mantienen el alcance empresarial, el estatus propio de la programación y las demás reglas de venta. ProgramacionService conserva la comprobación de catálogos activos al crear nuevas salidas. Cambiar un estatus de catálogo no modifica sus programaciones.
