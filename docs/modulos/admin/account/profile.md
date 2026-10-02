# Profile

Edición de los datos del administrador conectado.

- Clase: [Account/Profile.php](../../../../app/Livewire/Admin/Account/Profile.php).
- Vista: [livewire.admin.account.profile](../../../../resources/views/livewire/admin/account/profile.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

Exige sesión administrativa activa. Las rutas de cuenta están exceptuadas de permisos de módulos en [CheckPermission](../../../../app/Http/Middleware/CheckPermissionAdmin.php).

## Funcionamiento paso a paso

1. mount copia nombre, usuario y correo desde la sesión.
2. save exige la contraseña actual y valida usuario y correo únicos excluyendo la propia cuenta.
3. Guarda y audita dentro de una transacción; limpia la contraseña y muestra confirmación sin salir.

## Reglas y casos particulares

No necesita permiso de módulo: opera sobre el administrador autenticado.

## Validación del formulario

Reglas declaradas en línea. Las condiciones adicionales y las reglas multilínea se consultan en la clase enlazada.

~~~php
'current_password' => 'required|current_password:admin',
'name' => 'required|string|max:255',
'email' => ['required', 'email', Rule::unique('admins')->ignore($admin->id)],
'username' => ['required', 'string', 'min:3', 'max:100', Rule::unique('admins')->ignore($admin->id)],
~~~

## Métodos de referencia

`mount()`, `render()`, `save()`.

## Componentes de la vista

- `<x-form.title />`
- `<x-layout.error />`
- `<x-form.container-sm />`
- `<x-form.text-input />`
- `<x-layout.loader.fullpage />`

[Volver al índice administrativo](../README.md).
