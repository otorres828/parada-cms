# ListOrdenCobro

Consulta y descarga de órdenes de cobro.

- Clase: [OrdenesCobro/ListOrdenCobro.php](../../../../app/Livewire/Admin/OrdenesCobro/ListOrdenCobro.php).
- Vista: [livewire.admin.ordenes-cobro.list-orden-cobro](../../../../resources/views/livewire/admin/ordenes-cobro/list-orden-cobro.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermissionAdmin.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
$this->checkPermissions('ordenes-cobro', ['detail', 'review']);
Access::authorize('ordenes-cobro', 'download');
~~~

## Funcionamiento paso a paso

1. Inicializa rango predeterminado, orden descendente y permisos.
2. Filtra OrdenCobro::searchAdmin por texto, empresa, estado y fechas; pagina y calcula conversiones de la página.
3. exportExcel exige download y reconstruye el mismo filtro y orden para OrdenesCobroExport.

## Reglas y casos particulares

El Excel utiliza la consulta completa filtrada, no únicamente la página visible.

## Filtros y estado en URL

- `search`
- `empresa_id`
- `estatus`
- `date_from`
- `date_to`

## Métodos de referencia

`mount()`, `render()`, `updated()`, `exportExcel()`.

Listing aporta búsqueda, selección y ordenación. applySort valida que la columna exista y usa la clave primaria como respaldo. WithPagination gestiona la página; el flujo anterior indica cuándo se reinicia.

## Componentes de la vista

- `<x-list.heading />`
- `<x-list.actions />`
- `<x-list.search-input />`
- `<x-list.table />`
- `<x-list.view-button />`
- `<x-layout.loader.fullpage />`
- `<x-money.dual />`

[Volver al índice administrativo](../README.md).
