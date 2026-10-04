# ListProgramacion

Lista exclusivamente las programaciones de la empresa autenticada. Ofrece búsqueda, estatus, rango de fechas, ordenación y paginación. Las fechas se obtienen de los trayectos vendidos: salida más temprana y llegada más tardía. Esto admite programaciones que vendan únicamente combinaciones intermedias.

Agregar requiere permiso add; acceder a pasajeros requiere detail; editar y cambiar estatus requieren edit. Editar se muestra únicamente si la programación no tiene reservas ni está finalizada. Para esta condición se consulta existencia de reservas, sin cargarlas ni contarlas.

`changeStatus` autoriza edit, busca el ID dentro de la empresa y alterna entre 1 programada y 2 inactiva en una transacción. Los estados finalizado y eliminado no se reactivan. Finalizar no forma parte de esta acción. Emite successEventList después de guardar.

## Alcance y consultas

El middleware exige programaciones/list. PermissionsEmpresa resuelve permisos visuales en mount. La empresa se obtiene del usuario autenticado, sin selector ni propiedad pública para sustituirla. Los filtros reinician la página; el rango inicial es la última semana y la paginación limita cada página a 100 registros. La tabla empresarial es independiente de la administrativa.

## Integridad de terminales

ViajeTramo contiene todas las combinaciones de la plantilla, ordenadas por los bucles de generación, sin campos de posición adicionales. ProgramacionTramoPrecio conserva su propia copia de origen, destino, precio y horarios. Esta duplicidad es intencional y no debe sustituirse por viaje_tramo_id: editar la plantilla no puede cambiar los datos de una salida vendida.

La disponibilidad depende también del recorrido; una ruta con programaciones no puede cambiar sus paradas. Consultar [la regla completa](../../../servicios/logica-horarios-tramos.md#regla-de-diseño-terminales-propios-de-cada-programación).
