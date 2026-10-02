# ListTerminal

Consulta de terminales y su estado.

- Clase: [Terminales/ListTerminal.php](../../../../app/Livewire/Admin/Terminales/ListTerminal.php).
- Vista: [livewire.admin.terminales.list-terminal](../../../../resources/views/livewire/admin/terminales/list-terminal.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermission.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
$this->checkPermissions('terminales');
Access::authorize('terminales', 'edit');
~~~

## Funcionamiento paso a paso

1. Carga estados geográficos y permisos.
2. Filtra por búsqueda, estado geográfico y estatus; ordena y pagina.
3. changeStatus exige edit, bloquea el registro y alterna constantes activas/inactivas con auditoría.

## Reglas y casos particulares

estado_id identifica la ubicación geográfica; status es el filtro de habilitación.

## Filtros y estado en URL

- `estado_id`
- `search`
- `per_page`
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

[Volver al índice administrativo](../README.md).
