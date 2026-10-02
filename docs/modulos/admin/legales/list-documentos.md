# ListDocumentos

Selección de empresa para consultar su expediente documental.

- Clase: [Legales/ListDocumentos.php](../../../../app/Livewire/Admin/Legales/ListDocumentos.php).
- Vista: [livewire.admin.legales.list-documentos](../../../../resources/views/livewire/admin/legales/list-documentos.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermissionAdmin.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
$this->checkPermissions('legales', ['detail']);
~~~

## Funcionamiento paso a paso

1. mount calcula legales/detail y ordena por nombre.
2. Consulta empresas con con_legales, búsqueda, tipo de entidad y tipo de contrato.
3. Pagina las tarjetas de empresas y reinicia página al cambiar filtros.

## Reglas y casos particulares

Aquí se listan empresas; sus documentos individuales se consultan en EmpresaDocumento.

## Filtros y estado en URL

- `search`
- `tipo_entidad`
- `tipo_contrato`
- `per_page`

## Métodos de referencia

`mount()`, `render()`, `updated()`.

Listing aporta búsqueda, selección y ordenación. applySort valida que la columna exista y usa la clave primaria como respaldo. WithPagination gestiona la página; el flujo anterior indica cuándo se reinicia.

## Componentes de la vista

- `<x-layout.loader.fullpage />`
- `<x-list.actions />`
- `<x-list.heading />`
- `<x-list.search-input />`
- `<x-list.status-badge />`

[Volver al índice administrativo](../README.md).
