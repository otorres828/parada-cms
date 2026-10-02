# ListPasaje

Listado del panel de Empresas. Clase `app/Livewire/Empresas/Pasajes/ListPasaje.php` y vista `livewire.empresas.pasajes.list-pasaje`.

## Acceso y alcance

El middleware exige el permiso `pasajes/list`. En `mount` se resuelven los permisos de presentación con `PermissionsEmpresa`. Las acciones de descarga o escritura vuelven a autorizarse en el servidor.

La consulta obtiene `empresa_id` del usuario autenticado en el guard `empresa` en cada ejecución. No existe un selector ni una propiedad pública para elegir otra empresa. Los listados utilizan `searchAdmin`; los reportes utilizan consultas agregadas del modelo Reserva con el mismo alcance obligatorio.

## Funcionamiento

Búsqueda, rango de compra y estado de pago de la reserva. Descarga Excel con permiso download.

Los cambios de filtros reinician la página. La paginación limita cada página a 100 registros. Los módulos con fechas inician con la última semana y utilizan la normalización de fechas del modelo.

La vista conserva el encabezado, filtros y paginación del panel; la tabla está definida en su propia vista de Empresas, independiente de Admin. Solo se reutilizan elementos básicos de presentación como buscadores, botones, montos y contenedores. En Empresas no se muestra la columna Empresa ni se generan enlaces a rutas de detalle o edición que todavía no existen. Los formularios y detalles se implementarán por separado.

## Columnas por contrato

Con `CONTRATO_ELLOS_RECIBEN`, la vista muestra `subtotal` con el encabezado Total, oculta Tasa de servicio y el Total original. Para el otro contrato conserva las tres columnas. La condición se obtiene de la empresa autenticada en render, sin modificar los importes almacenados. Esta regla es de presentación de la vista.

El Excel utiliza `App\Exports\Empresas\PasajesExport`, separado del export de Admin. Aplica la misma presentación por contrato: con Ellos reciben, Total contiene el subtotal y se omiten tasa y total con tasa, en ambas monedas. Conserva lectura en chunks de 1000 y tratamiento de texto contra fórmulas. Los adjuntos de órdenes de cobro conservan el formato completo de Admin para detallar las tasas cobradas.
