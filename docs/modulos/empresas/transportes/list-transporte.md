# ListTransporte

Listado del panel de Empresas. Clase `app/Livewire/Empresas/Transportes/ListTransporte.php` y vista `livewire.empresas.transportes.list-transporte`.

## Acceso y alcance

El middleware exige el permiso `transportes/list`. En `mount` se resuelven los permisos de presentación con `PermissionsEmpresa`. Las acciones de descarga o escritura vuelven a autorizarse en el servidor.

La consulta obtiene `empresa_id` del usuario autenticado en el guard `empresa` en cada ejecución. No existe un selector ni una propiedad pública para elegir otra empresa. Los listados utilizan `searchAdmin`; los reportes utilizan consultas agregadas del modelo Reserva con el mismo alcance obligatorio.

## Funcionamiento

Búsqueda y estado.

Los cambios de filtros reinician la página. La paginación limita cada página a 100 registros. Los módulos con fechas inician con la última semana y utilizan la normalización de fechas del modelo.

La vista conserva el encabezado, filtros y paginación del panel; la tabla está definida en su propia vista de Empresas, independiente de Admin. Solo se reutilizan elementos básicos de presentación como buscadores, botones, montos y contenedores. En Empresas no se muestra la columna Empresa ni se generan enlaces a rutas de detalle o edición que todavía no existen. Los formularios y detalles se implementarán por separado.

El tipo de transporte se obtiene de la empresa autenticada: agencia de autobús usa `autobus` y conductor de carro usa `carro`. No es un filtro editable ni un parámetro de URL. Se aplica tanto al listado como a las exportaciones.

## Acciones de edición y estatus

El permiso transportes/edit habilita los componentes x-list.edit-button y x-list.status-button, como en Programaciones. No se muestran para plantillas. ChangeStatus vuelve a autorizar, consulta por empresa y tipo de transporte, bloquea la fila en una transacción y alterna activo (1) e inactivo (2). No elimina ni modifica programaciones; emite successEventList al terminar. La ruta de edición tiene su permiso correspondiente en CheckPermissionEmpresa.
