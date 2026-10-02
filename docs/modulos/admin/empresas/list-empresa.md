# ListEmpresa

Listado por tipo de entidad y estado de las empresas.

- Clase: [Empresas/ListEmpresa.php](../../../../app/Livewire/Admin/Empresas/ListEmpresa.php).
- Vista: [livewire.admin.empresas.list-empresa](../../../../resources/views/livewire/admin/empresas/list-empresa.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermissionAdmin.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
$this->checkPermissions('empresas', ['detail']);
Access::authorize('empresas', 'edit');
~~~

## Funcionamiento paso a paso

1. Carga permisos y orden inicial; consulta Empresa::searchAdmin con búsqueda, tipo de entidad y estado.
2. changeStatus exige empresas/edit antes de intentar activar o inactivar.
3. Si existe bloqueo por cobranza impide la activación y muestra un error; en caso permitido guarda y audita.

## Filtros y estado en URL

- `search`
- `per_page`
- `tipo_entidad`
- `status`

## Métodos de referencia

`mount()`, `render()`, `updated()`, `changeStatus()`.

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
- `<x-list.status-button />`
- `<x-list.table />`
- `<x-list.view-button />`

[Volver al índice administrativo](../README.md).
