# ListSolicitud

Bandeja de contactos recibidos desde el formulario público.

- Clase: [Solicitudes/ListSolicitud.php](../../../../app/Livewire/Admin/Solicitudes/ListSolicitud.php).
- Vista: [livewire.admin.solicitudes.list-solicitud](../../../../resources/views/livewire/admin/solicitudes/list-solicitud.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermission.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
$this->checkPermissions('solicitudes', ['detail']);
Access::authorize('solicitudes', 'delete');
~~~

## Funcionamiento paso a paso

1. Carga permisos y consulta SolicitudEmpresa::searchAdmin con búsqueda y estado.
2. deleteSolicitud exige delete y elimina el registro con auditoría.
3. deleteSolicitudes exige selección, convierte los IDs a enteros y elimina duplicados; consulta los registros existentes y los elimina dentro de una transacción, después limpia selección y confirma.

## Reglas y casos particulares

Las eliminaciones son físicas para permitir depurar spam; no son tickets con conversación.

## Filtros y estado en URL

- `search`
- `per_page`
- `estatus`

## Métodos de referencia

`mount()`, `render()`, `updated()`, `deleteSolicitud()`, `deleteSolicitudes()`.

Listing aporta búsqueda, selección y ordenación. applySort valida que la columna exista y usa la clave primaria como respaldo. WithPagination gestiona la página; el flujo anterior indica cuándo se reinicia.

## Componentes de la vista

- `<x-layout.loader.fullpage />`
- `<x-list.actions />`
- `<x-list.button-group />`
- `<x-list.check-all />`
- `<x-list.delete-all-button />`
- `<x-list.delete-button />`
- `<x-list.heading />`
- `<x-list.search-input />`
- `<x-list.sortable-button />`
- `<x-list.table />`
- `<x-list.view-button />`

[Volver al índice administrativo](../README.md).
