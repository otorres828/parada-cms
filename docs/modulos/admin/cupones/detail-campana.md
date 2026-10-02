# DetailCampana

Consulta de una campaña y sus códigos utilizados o disponibles.

- Clase: [Cupones/DetailCampana.php](../../../../app/Livewire/Admin/Cupones/DetailCampana.php).
- Vista: [livewire.admin.cupones.detail-campana](../../../../resources/views/livewire/admin/cupones/detail-campana.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermissionAdmin.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
Access::allows('reservas', 'detail');
~~~

## Funcionamiento paso a paso

1. mount fija ID, orden y permiso reservas/detail.
2. render consulta Cupon::searchAdmin restringido a la campaña y carga la reserva relacionada.
3. Aplica búsqueda, estado y paginación; los enlaces a reservas dependen del permiso calculado.

## Filtros y estado en URL

- `search`
- `status`
- `per_page`

## Métodos de referencia

`mount()`, `render()`, `updated()`, `findConfiguracionCupon()`.

Listing aporta búsqueda, selección y ordenación. applySort valida que la columna exista y usa la clave primaria como respaldo. WithPagination gestiona la página; el flujo anterior indica cuándo se reinicia.

## Componentes de la vista

- `<x-cupones.cupones-table />`
- `<x-cupones.description />`
- `<x-form.cancel-button />`
- `<x-layout.error />`
- `<x-layout.loader.fullpage />`
- `<x-list.actions />`
- `<x-list.heading />`
- `<x-list.search-input />`

[Volver al índice administrativo](../README.md).
