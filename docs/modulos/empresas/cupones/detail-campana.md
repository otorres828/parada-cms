# DetailCampana

Consulta las condiciones de una campaña de la empresa autenticada y sus cupones generados. Extiende `EmpresaComponent` y reutiliza los componentes descriptivos y de tabla del admin.

La ruta `empresas.cupones.detail` recibe `configuracion_cupon_id` y exige el permiso `cupones.detail`. Las campañas ajenas o eliminadas responden 404. El listado muestra el botón de detalle según permiso.

Incluye búsqueda por código o ID, filtro disponible/redimido, ordenación y paginación. Cada consulta se restringe a la campaña validada. El conteo de cupones es independiente del filtro. En campañas personalizadas las instancias aparecen cuando el cliente aplica el código.

Los enlaces de reservas llevan a `empresas.reservas.detail`, solo con permiso de detalle de reservas. Las reservas cargadas también se restringen a la empresa. Incluye el botón Volver al listado.
