# ListCampana

Consulta de campañas promocionales.

- Clase: [Cupones/ListCampana.php](../../../../app/Livewire/Admin/Cupones/ListCampana.php).
- Vista: [livewire.admin.cupones.list-campana](../../../../resources/views/livewire/admin/cupones/list-campana.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermission.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
$this->checkPermissions('cupones', ['detail']);
Access::authorize('cupones', 'edit');
~~~

## Funcionamiento paso a paso

1. Carga permisos y fechas predeterminadas mediante TraitGeneral; ordena por ID descendente.
2. Consulta ConfiguracionCupon::searchAdmin con los filtros de la pantalla.
3. Aplica orden y paginación y reinicia página cuando cambia la búsqueda o los filtros.
4. changeStatus exige cupones/edit, bloquea la campaña, alterna estatus 1/2 y guarda auditoría en transacción; después confirma con una alerta.

## Filtros y estado en URL

- `search`
- `per_page`
- `status`
- `date_from`
- `date_to`

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
