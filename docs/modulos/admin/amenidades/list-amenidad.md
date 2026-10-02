# ListAmenidad

Consulta y activación de amenidades disponibles para los transportes.

- Clase: [Amenidades/ListAmenidad.php](../../../../app/Livewire/Admin/Amenidades/ListAmenidad.php).
- Vista: [livewire.admin.amenidades.list-amenidad](../../../../resources/views/livewire/admin/amenidades/list-amenidad.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermission.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
$this->checkPermissions('amenidades');
Access::authorize('amenidades', 'edit');
~~~

## Funcionamiento paso a paso

1. mount carga permisos y orden descendente por ID.
2. render aplica búsqueda y estado a Amenidad::searchAdmin, ordena y pagina.
3. changeStatus autoriza edit, bloquea el registro y alterna ESTADO_ACTIVE y ESTADO_INACTIVE dentro de una transacción.
4. Guarda auditoría y emite la alerta de éxito; cambiar filtros reinicia la página.

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
