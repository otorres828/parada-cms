# ListTransporte

Consulta de autobuses y carros.

- Clase: [Transportes/ListTransporte.php](../../../../app/Livewire/Admin/Transportes/ListTransporte.php).
- Vista: [livewire.admin.transportes.list-transporte](../../../../resources/views/livewire/admin/transportes/list-transporte.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermissionAdmin.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
$this->checkPermissions('transportes', ['detail']);
~~~

## Funcionamiento paso a paso

1. Carga empresas y permiso de detalle.
2. Filtra por empresa, tipo de transporte, búsqueda y estado.
3. Ordena y pagina; reinicia página al cambiar filtros.

## Reglas y casos particulares

Es un módulo de supervisión: la clase no crea ni modifica vehículos.

## Filtros y estado en URL

- `empresa_id`
- `search`
- `per_page`
- `status`
- `tipo_transporte`

## Métodos de referencia

`mount()`, `render()`, `updated()`.

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

[Volver al índice administrativo](../README.md).
