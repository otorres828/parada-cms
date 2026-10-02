# DetailPasaje

Lectura de un boleto y su QR.

- Clase: [Pasajes/DetailPasaje.php](../../../../app/Livewire/Admin/Pasajes/DetailPasaje.php).
- Vista: [livewire.admin.pasajes.detail-pasaje](../../../../resources/views/livewire/admin/pasajes/detail-pasaje.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermissionAdmin.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
Access::allows('cupones', 'detail');
Access::allows('reservas', 'detail');
~~~

## Funcionamiento paso a paso

1. mount calcula permisos de campaña y reserva y recupera el pasaje con esas relaciones y tipo de cambio.
2. Genera QR mediante getQr y usa una cadena vacía si no está disponible.
3. render entrega el detalle con el snapshot histórico del viajero y los importes.

## Reglas y casos particulares

getQr solo entrega código para reserva pagada con localizador; el enlace al viajero actual no reemplaza el snapshot del boleto.

## Métodos de referencia

`mount()`, `render()`, `findPasaje()`.

## Componentes de la vista

- `<x-form.cancel-button />`
- `<x-layout.loader.fullpage />`
- `<x-list.heading />`
- `<x-pasajes.description />`

[Volver al índice administrativo](../README.md).
