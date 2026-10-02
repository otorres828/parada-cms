# ListReembolso

Supervisión y exportación de reembolsos.

- Clase: [Reembolsos/ListReembolso.php](../../../../app/Livewire/Admin/Reembolsos/ListReembolso.php).
- Vista: [livewire.admin.reembolsos.list-reembolso](../../../../resources/views/livewire/admin/reembolsos/list-reembolso.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermission.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
$this->checkPermissions('reembolsos', ['detail']);
Access::allows('reservas', 'detail');
Access::authorize('reembolsos', 'download');
~~~

## Funcionamiento paso a paso

1. Inicializa permisos, fechas y empresas.
2. Consulta Reembolso::searchAdmin con los filtros elegidos y pagina.
3. exportExcel exige reembolsos/download y exporta la consulta filtrada y ordenada.

## Reglas y casos particulares

Este listado administrativo no crea, aprueba ni paga reembolsos.

## Filtros y estado en URL

- `empresa_id`
- `search`
- `per_page`
- `status`
- `date_from`
- `date_to`

## Métodos de referencia

`mount()`, `render()`, `exportExcel()`, `updated()`.

Listing aporta búsqueda, selección y ordenación. applySort valida que la columna exista y usa la clave primaria como respaldo. WithPagination gestiona la página; el flujo anterior indica cuándo se reinicia.

## Componentes de la vista

- `<x-layout.loader.fullpage />`
- `<x-list.actions />`
- `<x-list.button-group />`
- `<x-list.heading />`
- `<x-list.search-input />`
- `<x-list.sortable-button />`
- `<x-list.status-badge />`
- `<x-list.table />`
- `<x-list.view-button />`
- `<x-money.dual />`

[Volver al índice administrativo](../README.md).
