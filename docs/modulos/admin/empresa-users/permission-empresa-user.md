# PermissionEmpresaUser

Asignación de permisos a un usuario de empresa.

- Clase: [EmpresaUsers/PermissionEmpresaUser.php](../../../../app/Livewire/Admin/EmpresaUsers/PermissionEmpresaUser.php).
- Vista: [livewire.admin.empresa-users.permission-empresa-user](../../../../resources/views/livewire/admin/empresa-users/permission-empresa-user.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermissionAdmin.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
Access::authorize('empresas.users', 'permissions');
~~~

## Funcionamiento paso a paso

1. mount valida empresa, carga permisos activos agrupados y selección actual del usuario.
2. savePermissions exige empresas.users/permissions y valida IDs enteros, distintos y existentes.
3. Dentro de la transacción vuelve a comprobar que sean asignables, sincroniza la relación y audita.
4. Vuelve al listado de esa empresa.

## Reglas y casos particulares

La búsqueda del usuario siempre incluye empresa_id; sync reemplaza su selección completa, no solo agrega los nuevos.

## Validación del formulario

Reglas declaradas en línea. Las condiciones adicionales y las reglas multilínea se consultan en la clase enlazada.

~~~php
'selectedPermissions.*' => 'integer|distinct|exists:permissions_empresa,id',
~~~

## Métodos de referencia

`mount()`, `render()`, `savePermissions()`, `findUsuarioEmpresa()`.

## Componentes de la vista

- `<x-list.heading />`
- `<x-form.cancel-button />`
- `<x-layout.error />`
- `<x-layout.loader.fullpage />`

[Volver al índice administrativo](../README.md).
