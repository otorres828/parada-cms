# DetailSolicitud

Consulta de un contacto y seguimiento de su estado.

- Clase: [Solicitudes/DetailSolicitud.php](../../../../app/Livewire/Admin/Solicitudes/DetailSolicitud.php).
- Vista: [livewire.admin.solicitudes.detail-solicitud](../../../../resources/views/livewire/admin/solicitudes/detail-solicitud.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermissionAdmin.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
Access::allows('solicitudes', 'edit');
Access::authorize('solicitudes', 'edit');
~~~

## Funcionamiento paso a paso

1. mount carga solicitud y permiso solicitudes/edit.
2. render muestra la información actual.
3. save exige edit, valida el estado permitido, actualiza y registra auditoría.
4. Permanece en el detalle y muestra confirmación.

## Métodos de referencia

`mount()`, `render()`, `save()`.

## Componentes de la vista

- `<x-form.cancel-button />`
- `<x-layout.error />`
- `<x-layout.loader.fullpage />`
- `<x-list.heading />`
- `<x-solicitudes.description />`

[Volver al índice administrativo](../README.md).
