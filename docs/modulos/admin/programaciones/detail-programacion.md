# DetailProgramacion

Consulta de pasajeros, importes y disponibilidad por tramo de una salida.

- Clase: [Programaciones/DetailProgramacion.php](../../../../app/Livewire/Admin/Programaciones/DetailProgramacion.php).
- Vista: [livewire.admin.programaciones.passenger-programacion](../../../../resources/views/livewire/admin/programaciones/passenger-programacion.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermissionAdmin.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
Access::allows('reservas', 'detail');
Access::allows('viajes', 'detail');
Access::allows('transportes', 'detail');
~~~

## Funcionamiento paso a paso

1. Carga programación con ruta, terminales, tarifas y amenidades y calcula permisos de enlaces.
2. En cada render vuelve a recuperar tickets y disponibilidad mediante ViajeTramo::disponibilidadPorTramos.
3. Separa pasajes estrictamente pagados y pendientes por estado de la reserva.
4. resumenPasajes calcula cantidad, total, tasas y conversiones históricas en bolívares.
5. Entrega capacidad acotada por programación y transporte junto con los resúmenes.

## Reglas y casos particulares

La colección de tickets se carga completa; esta clase no usa paginación. Los nuevos vigentes participan en disponibilidad aunque no se sumen a las filas de pagados o pendientes.

## Métodos de referencia

`mount()`, `render()`, `findProgramacion()`, `resumenPasajes()`.

## Componentes de la vista

- `<x-list.heading />`
- `<x-programacion.description />`
- `<x-programacion.rates-matrix />`
- `<x-programacion.passengers-table />`
- `<x-layout.loader.fullpage />`

[Volver al índice administrativo](../README.md).
