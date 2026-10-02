# ListAdmin

Listado de cuentas administrativas.

- Clase: [Admins/ListAdmin.php](../../../../app/Livewire/Admin/Admins/ListAdmin.php).
- Vista: [livewire.admin.admins.list-admin](../../../../resources/views/livewire/admin/admins/list-admin.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermissionAdmin.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
$this->checkPermissions('admins');
~~~

## Funcionamiento paso a paso

1. mount prepara orden descendente por ID y permisos de admins.
2. render consulta Admin::searchAdmin con búsqueda y estado y pagina el resultado ordenado.
3. Cambios en búsqueda, estado o tamaño reinician la página.

## Reglas y casos particulares

La clase no declara acciones de eliminación ni cambio de estado. La consulta del modelo determina qué niveles pueden aparecer.

## Filtros y estado en URL

- `search`
- `per_page`
- `status`

## Métodos de referencia

`mount()`, `render()`, `updated()`.

Listing aporta búsqueda, selección y ordenación. applySort valida que la columna exista y usa la clave primaria como respaldo. WithPagination gestiona la página; el flujo anterior indica cuándo se reinicia.

## Componentes de la vista

- `<x-layout.loader.fullpage />`
- `<x-list.actions />`
- `<x-list.add-button />`
- `<x-list.button-group />`
- `<x-list.edit-button />`
- `<x-list.heading />`
- `<x-list.search-input />`
- `<x-list.sortable-button />`
- `<x-list.status-badge />`
- `<x-list.table />`

[Volver al índice administrativo](../README.md).
