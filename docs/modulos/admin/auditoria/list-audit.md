# ListAudit

Consulta de acciones registradas por el sistema.

- Clase: [Auditoria/ListAudit.php](../../../../app/Livewire/Admin/Auditoria/ListAudit.php).
- Vista: [livewire.admin.auditoria.list-audit](../../../../resources/views/livewire/admin/auditoria/list-audit.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermissionAdmin.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
$this->checkPermissions('auditoria', ['detail']);
~~~

## Funcionamiento paso a paso

1. Inicializa permisos, orden y fechas predeterminadas mediante TraitGeneral.
2. Consulta Auditoria::searchAdmin y aplica filtros, ordenación y paginación.
3. Cambiar filtros reinicia la página.

## Reglas y casos particulares

Es una pantalla de lectura de eventos; no revierte los cambios auditados.

## Filtros y estado en URL

- `search`
- `per_page`
- `status`
- `date_from`
- `date_to`

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
- `<x-list.table />`
- `<x-list.view-button />`

[Volver al índice administrativo](../README.md).
