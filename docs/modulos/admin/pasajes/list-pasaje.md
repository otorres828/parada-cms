# ListPasaje

Consulta y exportación de pasajes y sus reservas.

- Clase: [Pasajes/ListPasaje.php](../../../../app/Livewire/Admin/Pasajes/ListPasaje.php).
- Vista: [livewire.admin.pasajes.list-pasaje](../../../../resources/views/livewire/admin/pasajes/list-pasaje.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermission.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
$this->checkPermissions('pasajes', ['detail']);
Access::authorize('pasajes', 'list');
Access::authorize('pasajes', 'detail');
~~~

## Funcionamiento paso a paso

1. Inicializa fechas predeterminadas y empresas disponibles.
2. Consulta Pasaje::searchAdmin: empresa, estado de pago y fechas se aplican a la reserva asociada.
3. Ordena y pagina; cambiar filtros reinicia la página.
4. exportExcel exige simultáneamente pasajes/list y pasajes/detail, registra la descarga y entrega PasajesExport con la consulta filtrada.

## Reglas y casos particulares

El permiso del Excel es list más detail, no un permiso download independiente.

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
- `<x-list.view-button />`
- `<x-money.dual />`

[Volver al índice administrativo](../README.md).
