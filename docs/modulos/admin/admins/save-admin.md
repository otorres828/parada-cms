# SaveAdmin

Alta o edición de administradores y asignación de permisos.

- Clase: [Admins/SaveAdmin.php](../../../../app/Livewire/Admin/Admins/SaveAdmin.php).
- Vista: [livewire.admin.admins.save-admin](../../../../resources/views/livewire/admin/admins/save-admin.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermission.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
Access::authorize('admins', $this->admin_id ? 'edit' : 'add');
~~~

## Funcionamiento paso a paso

1. mount carga grupos asignables y, si hay ID, recupera el administrador mediante searchAdmin.
2. save exige add o edit y valida identidad, contraseña y selección de permisos.
3. En transacción bloquea los administradores, preserva nivel root, impide desactivar la cuenta propia y descarta permisos inactivos o reservados.
4. Guarda, exige al menos un root activo, sincroniza permisos y audita; regresa al listado.

## Reglas y casos particulares

Que el formulario tenga lógica para preservar root no garantiza que searchAdmin permita recuperarlo. La contraseña vacía al editar conserva la anterior.

## Validación del formulario

Reglas declaradas en línea. Las condiciones adicionales y las reglas multilínea se consultan en la clase enlazada.

~~~php
'name' => 'required|string|max:255',
'username' => ['required', 'string', 'min:3', 'max:100', Rule::unique('admins', 'username')->ignore($this->admin_id)],
'email' => ['required', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($this->admin_id)],
'password' => [$this->admin_id ? 'nullable' : 'required', 'string', 'min:10', 'max:255'],
'status' => 'required|in:1,2',
'is_superadmin' => 'boolean',
'selectedPermissions.*' => 'integer|distinct|exists:permissions_admin,id',
~~~

## Métodos de referencia

`mount()`, `render()`, `save()`, `editar()`.

## Componentes de la vista

- `<x-form.title />`
- `<x-layout.error />`
- `<x-form.container-sm />`
- `<x-form.text-input />`
- `<x-form.switch />`
- `<x-form.dropdown />`
- `<x-form.subtitle />`
- `<x-form.cancel-button />`
- `<x-layout.loader.fullpage />`

[Volver al índice administrativo](../README.md).
