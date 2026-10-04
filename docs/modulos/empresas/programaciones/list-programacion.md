# ListProgramacion

Listado del panel de Empresas. Clase `app/Livewire/Empresas/Programaciones/ListProgramacion.php` y vista `livewire.empresas.programaciones.list-programacion`.

## Acceso y alcance

El middleware exige el permiso `programaciones/list`. En `mount` se resuelven los permisos de presentación con `PermissionsEmpresa`. Las acciones de descarga o escritura vuelven a autorizarse en el servidor.

La consulta obtiene `empresa_id` del usuario autenticado en el guard `empresa` en cada ejecución. No existe un selector ni una propiedad pública para elegir otra empresa. Los listados utilizan `searchAdmin`; los reportes utilizan consultas agregadas del modelo Reserva con el mismo alcance obligatorio.

## Funcionamiento

Búsqueda, rango de salida y estado de programación.

Los cambios de filtros reinician la página. La paginación limita cada página a 100 registros. Los módulos con fechas inician con la última semana y utilizan la normalización de fechas del modelo.

La vista conserva el encabezado, filtros y paginación del panel; la tabla está definida en su propia vista de Empresas, independiente de Admin. Solo se reutilizan elementos básicos de presentación como buscadores, botones, montos y contenedores. En Empresas no se muestra la columna Empresa ni se generan enlaces a rutas de detalle o edición que todavía no existen. Los formularios y detalles se implementarán por separado.

## Regla de integridad de los tramos

`viaje_tramos` describe el recorrido; `programacion_tramo_precios` conserva los terminales, precios y horarios de los trayectos vendibles de cada salida. Que ambas tablas tengan origen y destino es intencional: evita depender de los extremos de un tramo mutable y permite vender trayectos que abarcan varios segmentos. No reemplazarlos directamente por `viaje_tramo_id`.

La disponibilidad todavía depende del recorrido de la ruta, por lo que conservar estos campos no protege por sí solo frente a cambios de paradas. Consultar la [regla completa de terminales y protección del recorrido](../../../servicios/logica-horarios-tramos.md#regla-de-diseño-terminales-propios-de-cada-programación) antes de implementar ediciones o eliminaciones.
