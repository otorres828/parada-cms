# ListPregunta

Listado de artículos breves de ayuda.

- Clase: [PreguntasFrecuentes/ListPregunta.php](../../../../app/Livewire/Admin/PreguntasFrecuentes/ListPregunta.php).
- Vista: [livewire.admin.preguntas-frecuentes.list](../../../../resources/views/livewire/admin/preguntas-frecuentes/list.blade.php).
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

1. Carga categorías y permisos; ordena por orden ascendente.
2. Filtra por búsqueda, categoría, destacada y estatus y pagina.
3. changeStatus exige edit y alterna 1/2 con auditoría.
4. deletePregunta exige delete y marca estado eliminado 0 conservando la fila.

## Filtros y estado en URL

- `search`
- `per_page`
- `categoria_id`
- `destacada`
- `estatus`

## Métodos de referencia

`mount()`, `render()`, `updated()`, `changeStatus()`, `deletePregunta()`.

Listing aporta búsqueda, selección y ordenación. applySort valida que la columna exista y usa la clave primaria como respaldo. WithPagination gestiona la página; el flujo anterior indica cuándo se reinicia.

## Componentes de la vista

- `<x-list.heading />`
- `<x-list.actions />`
- `<x-list.search-input />`
- `<x-list.table />`
- `<x-list.sortable-button />`
- `<x-list.status-badge />`
- `<x-list.button-group />`
- `<x-list.status-button />`
- `<x-list.edit-button />`
- `<x-list.delete-button />`
- `<x-layout.loader.fullpage />`
- `<x-list.add-button />`

[Volver al índice administrativo](../README.md).
