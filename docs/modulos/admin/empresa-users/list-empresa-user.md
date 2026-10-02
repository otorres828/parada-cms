# ListEmpresaUser

Consulta de usuarios dentro de una empresa.

- Clase: [EmpresaUsers/ListEmpresaUser.php](../../../../app/Livewire/Admin/EmpresaUsers/ListEmpresaUser.php).
- Vista: [livewire.admin.empresa-users.list-empresa-user](../../../../resources/views/livewire/admin/empresa-users/list-empresa-user.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermission.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
$this->checkPermissions('empresas.users', ['detail', 'permissions']);
Access::authorize('empresas.users', 'edit');
~~~

## Funcionamiento paso a paso

1. Valida la empresa recibida y carga permisos de empresas.users.
2. Filtra usuarios por empresa, búsqueda y estado y pagina el resultado.
3. changeStatus vuelve a exigir edit y limita la consulta a esa misma empresa.

## Reglas y casos particulares

No tiene filtro de fechas ni ruta administrativa para alta de usuarios.

## Filtros y estado en URL

- `search`
- `per_page`
- `status`

## Métodos de referencia

`mount()`, `render()`, `updated()`, `changeStatus()`.

Listing aporta búsqueda, selección y ordenación. applySort valida que la columna exista y usa la clave primaria como respaldo. WithPagination gestiona la página; el flujo anterior indica cuándo se reinicia.

## Componentes de la vista

- `<x-layout.loader.fullpage />`
- `<x-list.actions />`
- `<x-list.button-group />`
- `<x-list.edit-button />`
- `<x-list.heading />`
- `<x-list.search-input />`
- `<x-list.sortable-button />`
- `<x-list.status-badge />`
- `<x-list.status-button />`
- `<x-list.table />`
- `<x-list.view-button />`

[Volver al índice administrativo](../README.md).
