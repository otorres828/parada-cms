# ListCliente

Consulta de clientes y cambio de estado.

- Clase: [Clientes/ListCliente.php](../../../../app/Livewire/Admin/Clientes/ListCliente.php).
- Vista: [livewire.admin.clientes.list-cliente](../../../../resources/views/livewire/admin/clientes/list-cliente.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermission.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
$this->checkPermissions('clientes', ['detail']);
Access::authorize('clientes', 'edit');
~~~

## Funcionamiento paso a paso

1. Carga permisos y orden inicial.
2. Consulta User::searchAdmin con búsqueda y estado, ordena y pagina.
3. changeStatus exige clientes/edit, modifica el estado y registra auditoría.
4. Reinicia paginación al modificar filtros.

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
