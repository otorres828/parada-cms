# DetailViaje

Consulta de un recorrido, sus tramos y salidas.

- Clase: [Viajes/DetailViaje.php](../../../../app/Livewire/Admin/Viajes/DetailViaje.php).
- Vista: [livewire.admin.viajes.detail-viaje](../../../../resources/views/livewire/admin/viajes/detail-viaje.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermission.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
Access::allows('programaciones', 'passengers');
~~~

## Funcionamiento paso a paso

1. mount busca ruta con empresa, terminales, tramos y tarifas; si no existe responde 404.
2. Calcula permiso para ver pasajeros de las programaciones.
3. render usa Programacion::searchDetailViajes y pagina salidas asociadas.
4. Muestra el tipo de cambio vigente; cambiar per_page reinicia la página.

## Filtros y estado en URL

- `per_page`

## Métodos de referencia

`mount()`, `render()`, `updatedPerPage()`.

Listing aporta búsqueda, selección y ordenación. applySort valida que la columna exista y usa la clave primaria como respaldo. WithPagination gestiona la página; el flujo anterior indica cuándo se reinicia.

## Componentes de la vista

- `<x-form.cancel-button />`
- `<x-layout.loader.fullpage />`
- `<x-list.heading />`
- `<x-viajes.description />`
- `<x-viajes.programaciones-table />`
- `<x-viajes.tramo-precios-table />`

[Volver al índice administrativo](../README.md).
