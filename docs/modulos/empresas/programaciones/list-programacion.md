# ListProgramacion

Listado del panel de Empresas. Clase `app/Livewire/Empresas/Programaciones/ListProgramacion.php` y vista `livewire.empresas.programaciones.list-programacion`.

## Acceso y alcance

El middleware exige el permiso `programaciones/list`. En `mount` se resuelven los permisos de presentación con `PermissionsEmpresa`. Las acciones de descarga o escritura vuelven a autorizarse en el servidor.

La consulta obtiene `empresa_id` del usuario autenticado en el guard `empresa` en cada ejecución. No existe un selector ni una propiedad pública para elegir otra empresa. Los listados utilizan `searchAdmin`; los reportes utilizan consultas agregadas del modelo Reserva con el mismo alcance obligatorio.

## Funcionamiento

Búsqueda, rango de salida y estado de programación.

Los cambios de filtros reinician la página. La paginación limita cada página a 100 registros. Los módulos con fechas inician con la última semana y utilizan la normalización de fechas del modelo.

La vista conserva el encabezado, filtros y paginación del panel; la tabla está definida en su propia vista de Empresas, independiente de Admin. Solo se reutilizan elementos básicos de presentación como buscadores, botones, montos y contenedores. En Empresas no se muestra la columna Empresa ni se generan enlaces a rutas de detalle o edición que todavía no existen. Los formularios y detalles se implementarán por separado.
