# ListViaje

Consulta rutas de la empresa autenticada con búsqueda, estatus, ordenación y paginación. Los permisos determinan agregar, detalle, editar y cambiar estatus.

`changeStatus` exige el permiso editar de viajes, busca el registro dentro de la empresa, bloquea la fila durante la transacción y alterna 1 activo / 2 inactivo. No elimina la ruta ni modifica sus programaciones o reservas. Un ID de otra empresa se rechaza. Emite successEventList con la confirmación.

El middleware exige viajes/list y PermissionsEmpresa resuelve los permisos visuales en mount. Cada consulta toma empresa_id del usuario autenticado; no hay selector empresarial. Los cambios de filtros reinician la página y cada página limita sus registros a 100. La vista utiliza su propia tabla empresarial y los componentes básicos compartidos; permite navegar al detalle o formulario según permisos.
