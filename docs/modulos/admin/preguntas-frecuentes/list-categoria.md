# ListCategoria

Consulta y mantenimiento de categorías del centro de ayuda.

- Clase: [PreguntasFrecuentes/ListCategoria.php](../../../../app/Livewire/Admin/PreguntasFrecuentes/ListCategoria.php).
- Vista: [livewire.admin.preguntas-frecuentes.list-categoria](../../../../resources/views/livewire/admin/preguntas-frecuentes/list-categoria.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermissionAdmin.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
$this->checkPermissions('preguntas-frecuentes');
Access::authorize('preguntas-frecuentes', 'edit');
Access::authorize('preguntas-frecuentes', 'delete');
~~~

## Funcionamiento paso a paso

1. Ordena por orden ascendente y filtra búsqueda y estado.
2. changeStatus exige edit y alterna los estados 1 y 2 dentro de una transacción con auditoría.
3. deleteCategoria exige delete y rechaza eliminar categorías que todavía tengan preguntas no eliminadas.
4. Si está vacía marca la categoría con estado eliminado y muestra confirmación.

## Filtros y estado en URL

- `search`
- `per_page`
- `estatus`

## Métodos de referencia

`mount()`, `render()`, `updated()`, `changeStatus()`, `deleteCategoria()`.

Listing aporta búsqueda, selección y ordenación. applySort valida que la columna exista y usa la clave primaria como respaldo. WithPagination gestiona la página; el flujo anterior indica cuándo se reinicia.

## Componentes de la vista

- `<x-list.heading />`
- `<x-list.actions />`
- `<x-list.table />`
- `<x-list.status-badge />`
- `<x-list.button-group />`
- `<x-list.status-button />`
- `<x-list.edit-button />`
- `<x-list.delete-button />`
- `<x-layout.loader.fullpage />`
- `<x-form.cancel-button />`
- `<x-list.search-input />`
- `<x-list.add-button />`

[Volver al índice administrativo](../README.md).
