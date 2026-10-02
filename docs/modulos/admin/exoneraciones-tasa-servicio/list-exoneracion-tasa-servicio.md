# ListExoneracionTasaServicio

Consulta y gestión de exoneraciones de tasa.

- Clase: [ExoneracionesTasaServicio/ListExoneracionTasaServicio.php](../../../../app/Livewire/Admin/ExoneracionesTasaServicio/ListExoneracionTasaServicio.php).
- Vista: [livewire.admin.exoneraciones-tasa-servicio.list-exoneracion-tasa-servicio](../../../../resources/views/livewire/admin/exoneraciones-tasa-servicio/list-exoneracion-tasa-servicio.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermissionAdmin.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
$this->checkPermissions('exoneraciones-tasa-servicio');
Access::authorize('exoneraciones-tasa-servicio', 'edit');
Access::authorize('exoneraciones-tasa-servicio', 'delete');
~~~

## Funcionamiento paso a paso

1. Ordena por fecha de inicio descendente y filtra por empresa, texto y estado.
2. changeStatus exige edit, bloquea y alterna activo/inactivo; valida solapamientos antes de guardar.
3. Un conflicto de fechas se convierte en alerta y revierte la transacción.
4. deleteExoneracion exige delete y marca ELIMINADO; no borra físicamente.

## Filtros y estado en URL

- `search`
- `empresa_id`
- `status`
- `per_page`

## Métodos de referencia

`mount()`, `render()`, `updated()`, `changeStatus()`, `deleteExoneracion()`.

Listing aporta búsqueda, selección y ordenación. applySort valida que la columna exista y usa la clave primaria como respaldo. WithPagination gestiona la página; el flujo anterior indica cuándo se reinicia.

## Componentes de la vista

- `<x-list.heading />`
- `<x-list.actions />`
- `<x-list.search-input />`
- `<x-list.add-button />`
- `<x-list.table />`
- `<x-list.status-badge />`
- `<x-list.button-group />`
- `<x-list.status-button />`
- `<x-list.edit-button />`
- `<x-list.delete-button />`
- `<x-layout.loader.fullpage />`

[Volver al índice administrativo](../README.md).
