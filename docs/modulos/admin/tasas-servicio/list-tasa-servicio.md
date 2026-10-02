# ListTasaServicio

Listado de rangos de tasa de servicio.

- Clase: [TasasServicio/ListTasaServicio.php](../../../../app/Livewire/Admin/TasasServicio/ListTasaServicio.php).
- Vista: [livewire.admin.tasas-servicio.list-tasa-servicio](../../../../resources/views/livewire/admin/tasas-servicio/list-tasa-servicio.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermissionAdmin.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
$this->checkPermissions('tasas-servicio');
Access::authorize('tasas-servicio', 'edit');
~~~

## Funcionamiento paso a paso

1. Inicializa permisos y orden ascendente por monto mínimo.
2. Filtra por texto y estado mediante TasaServicio::searchAdmin y pagina.
3. changeStatus exige edit, alterna 1/2, guarda y audita dentro de una transacción.
4. Errores de validación se muestran como alerta.

## Filtros y estado en URL

- `search`
- `status`
- `per_page`

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
