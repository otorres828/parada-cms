# ListProgramacion

Consulta de salidas programadas.

- Clase: [Programaciones/ListProgramacion.php](../../../../app/Livewire/Admin/Programaciones/ListProgramacion.php).
- Vista: [livewire.admin.programaciones.list-programacion](../../../../resources/views/livewire/admin/programaciones/list-programacion.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermissionAdmin.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
$this->checkPermissions('programaciones');
Access::allows('programaciones', 'detail');
~~~

## Funcionamiento paso a paso

1. Carga permisos y catálogos y prepara filtros.
2. Consulta Programacion::searchAdmin, ordena y pagina las salidas.
3. Cambiar filtros devuelve a la primera página.

## Reglas y casos particulares

El detalle operativo se abre mediante DetailProgramacion. El listado no crea rutas ni asigna asientos.

## Filtros y estado en URL

- `empresa_id`
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
- `<x-list.status-programacion />`
- `<x-list.table />`

[Volver al índice administrativo](../README.md).

## Regla de integridad de los tramos

`viaje_tramos` describe el recorrido; `programacion_tramo_precios` conserva los terminales, precios y horarios de los trayectos vendibles de cada salida. Que ambas tablas tengan origen y destino es intencional: conserva los extremos propios de la salida frente a modificaciones de la plantilla. `viaje_tramos` ahora incluye todas las combinaciones con sus posiciones; el recorrido se reconstruye usando las consecutivas. No reemplazarlos directamente por `viaje_tramo_id`.

La disponibilidad todavía depende del recorrido de la ruta, por lo que conservar estos campos no protege por sí solo frente a cambios de paradas. Consultar la [regla completa de terminales y protección del recorrido](../../../servicios/logica-horarios-tramos.md#regla-de-diseño-terminales-propios-de-cada-programación) antes de implementar ediciones o eliminaciones.
