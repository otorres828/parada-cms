# ListViaje

Consulta de rutas de viaje por empresa.

- Clase: [Viajes/ListViaje.php](../../../../app/Livewire/Admin/Viajes/ListViaje.php).
- Vista: [livewire.admin.viajes.list-viaje](../../../../resources/views/livewire/admin/viajes/list-viaje.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermission.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
$this->checkPermissions('viajes', ['detail']);
~~~

## Funcionamiento paso a paso

1. Carga empresas y permiso de detalle.
2. Consulta Viaje::searchAdmin con con_tasas, búsqueda, empresa y estado.
3. Ordena y pagina; filtros nuevos reinician la página.

## Reglas y casos particulares

No crea recorridos: permite supervisar las rutas registradas.

## Filtros y estado en URL

- `empresa_id`
- `search`
- `per_page`
- `status`

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
