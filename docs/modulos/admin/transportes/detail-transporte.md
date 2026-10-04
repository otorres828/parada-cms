# DetailTransporte

Consulta de un transporte y su historial comercial.

- Clase: [Transportes/DetailTransporte.php](../../../../app/Livewire/Admin/Transportes/DetailTransporte.php).
- Vista: [livewire.admin.transportes.detail-transporte](../../../../resources/views/livewire/admin/transportes/detail-transporte.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermissionAdmin.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
Access::allows('programaciones', 'detail');
~~~

## Funcionamiento paso a paso

1. Carga transporte con empresa y amenidades y permiso programaciones/passengers.
2. render consulta programaciones de ese transporte con historial_ventas.
3. Ordena por fecha, hora e ID descendente y limita el tamaño de página entre 1 y 100.
4. Entrega tipo de cambio vigente para la presentación y reinicia página si cambia per_page.

## Métodos de referencia

`mount()`, `render()`, `updatedPerPage()`, `findTransporte()`.

## Componentes de la vista

- `<x-transportes.description />`
- `<x-transportes.programaciones-table />`
- `<x-form.cancel-button />`
- `<x-layout.loader.fullpage />`
- `<x-list.heading />`

[Volver al índice administrativo](../README.md).
