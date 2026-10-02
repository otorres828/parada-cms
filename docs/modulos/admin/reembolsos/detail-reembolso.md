# DetailReembolso

Consulta de una solicitud de reembolso y su comprobante.

- Clase: [Reembolsos/DetailReembolso.php](../../../../app/Livewire/Admin/Reembolsos/DetailReembolso.php).
- Vista: [livewire.admin.reembolsos.detail-reembolso](../../../../resources/views/livewire/admin/reembolsos/detail-reembolso.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermission.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
Access::authorize('reembolsos', 'detail');
~~~

## Funcionamiento paso a paso

1. mount guarda el ID; render obtiene el reembolso con findReembolso.
2. downloadProof vuelve a autorizar reembolsos/detail y comprueba la disponibilidad del comprobante.
3. Exige una ruta con prefijo comprobantes/ existente en el disco local y devuelve su descarga; si no existe responde 404.

## Reglas y casos particulares

No contiene acciones de resolución financiera.

## Métodos de referencia

`mount()`, `render()`, `downloadProof()`, `findReembolso()`.

## Componentes de la vista

- `<x-form.cancel-button />`
- `<x-layout.loader.fullpage />`
- `<x-list.heading />`
- `<x-reembolsos.description />`

[Volver al índice administrativo](../README.md).
