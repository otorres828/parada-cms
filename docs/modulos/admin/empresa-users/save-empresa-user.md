# SaveEmpresaUser

Edición de un usuario empresarial.

- Clase: [EmpresaUsers/SaveEmpresaUser.php](../../../../app/Livewire/Admin/EmpresaUsers/SaveEmpresaUser.php).
- Vista: [livewire.admin.empresa-users.save-empresa-user](../../../../resources/views/livewire/admin/empresa-users/save-empresa-user.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermissionAdmin.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
Access::authorize('empresas.users', $this->usuario_empresa_id ? 'edit' : 'add');
~~~

## Funcionamiento paso a paso

1. mount comprueba empresa y obtiene al usuario con findAdminByCompany.
2. save exige edit para un usuario existente; valida correo único, nombre, contraseña opcional y banderas.
3. Actualiza dentro de una transacción, preserva la contraseña si está vacía y audita.
4. Regresa al listado de usuarios de la empresa.

## Reglas y casos particulares

El código conserva una rama de creación, pero routes/admin.php solo expone edición. La validación actual trata estatus como booleano.

## Validación del formulario

Reglas declaradas en línea. Las condiciones adicionales y las reglas multilínea se consultan en la clase enlazada.

~~~php
'nombre' => 'required|string|max:255',
'email' => ['required', 'email', Rule::unique('usuarios_empresa', 'email')->ignore($this->usuario_empresa_id)],
'password' => [$this->usuario_empresa_id ? 'nullable' : 'required', 'string', 'min:10', 'max:255'],
'es_admin' => 'required|boolean',
'estatus' => 'required|boolean',
~~~

## Métodos de referencia

`mount()`, `render()`, `save()`, `editar()`.

## Componentes de la vista

- `<x-list.heading />`
- `<x-form.cancel-button />`
- `<x-layout.error />`
- `<x-form.container-sm />`
- `<x-form.text-input />`
- `<x-form.dropdown />`
- `<x-layout.loader.fullpage />`

[Volver al índice administrativo](../README.md).
