# ListReserva

Consulta y exportación de compras.

- Clase: [Reservas/ListReserva.php](../../../../app/Livewire/Admin/Reservas/ListReserva.php).
- Vista: [livewire.admin.reservas.list-reserva](../../../../resources/views/livewire/admin/reservas/list-reserva.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermissionAdmin.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
$this->checkPermissions('reservas', ['detail']);
Access::authorize('reservas', 'download');
~~~

## Funcionamiento paso a paso

1. Inicializa empresas, fechas predeterminadas, orden y permisos.
2. Reserva::searchAdmin aplica búsqueda, empresa, estado de pago y rango; se pagina la consulta ordenada.
3. exportExcel exige reservas/download y usa ReservasExport con los mismos filtros y orden.
4. Los cambios de filtros reinician la página.

## Reglas y casos particulares

El origen y destino mostrados corresponden al tramo comprado; la cantidad de pasajes pertenece a cada reserva.

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
- `<x-list.status-reserva />`
- `<x-list.table />`
- `<x-money.dual />`

[Volver al índice administrativo](../README.md).
