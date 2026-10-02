# DetailAudit

Lectura de un evento de auditoría.

- Clase: [Auditoria/DetailAudit.php](../../../../app/Livewire/Admin/Auditoria/DetailAudit.php).
- Vista: [livewire.admin.auditoria.detail-audit](../../../../resources/views/livewire/admin/auditoria/detail-audit.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermissionAdmin.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

## Funcionamiento paso a paso

1. mount conserva audit_id y carga la auditoría mediante findAuditoria.
2. render entrega la vista del detalle.
3. Un ID no recuperable por la consulta termina en 404.

## Reglas y casos particulares

No contiene acciones para editar o borrar el evento.

## Métodos de referencia

`mount()`, `render()`, `findAuditoria()`.

## Componentes de la vista

- `<x-auditoria.description />`
- `<x-form.cancel-button />`
- `<x-layout.loader.fullpage />`
- `<x-list.heading />`

[Volver al índice administrativo](../README.md).
