# DetailReserva

Detalle financiero y de pasajeros de una reserva.

- Clase: [Reservas/DetailReserva.php](../../../../app/Livewire/Admin/Reservas/DetailReserva.php).
- Vista: [livewire.admin.reservas.detail-reserva](../../../../resources/views/livewire/admin/reservas/detail-reserva.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermissionAdmin.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
Access::allows('programaciones', 'passengers');
Access::allows('cupones', 'detail');
Access::allows('pasajes', 'detail');
Access::allows('reservas', 'detail');
~~~

## Funcionamiento paso a paso

1. mount calcula permisos para programación, campaña, pasaje y reservas relacionadas.
2. findReserva delega la carga del detalle en el modelo.
3. render entrega la descripción, importes y pasajes a los componentes Blade.

## Reglas y casos particulares

Los enlaces se habilitan según los permisos calculados. No crea pasajeros ni confirma pagos desde esta clase.

## Métodos de referencia

`mount()`, `render()`, `findReserva()`.

## Componentes de la vista

- `<x-form.cancel-button />`
- `<x-layout.loader.fullpage />`
- `<x-list.heading />`
- `<x-reservas.description />`
- `<x-reservas.pasajes-table />`

[Volver al índice administrativo](../README.md).
