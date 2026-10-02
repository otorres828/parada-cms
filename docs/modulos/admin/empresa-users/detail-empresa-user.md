# DetailEmpresaUser

Consulta de un usuario empresarial y sus datos asociados.

- Clase: [EmpresaUsers/DetailEmpresaUser.php](../../../../app/Livewire/Admin/EmpresaUsers/DetailEmpresaUser.php).
- Vista: [livewire.admin.empresa-users.detail-empresa-user](../../../../resources/views/livewire/admin/empresa-users/detail-empresa-user.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermissionAdmin.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

## Funcionamiento paso a paso

1. mount conserva los identificadores de empresa y usuario y valida el ámbito.
2. render busca mediante findUsuarioEmpresa con filtro empresa_id.
3. Un usuario de otra empresa no se puede recuperar usando estos parámetros.

## Métodos de referencia

`mount()`, `render()`, `findUsuarioEmpresa()`.

## Componentes de la vista

- `<x-empresa-users.description />`
- `<x-form.cancel-button />`
- `<x-layout.loader.fullpage />`
- `<x-list.heading />`

[Volver al índice administrativo](../README.md).
