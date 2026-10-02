# Password

Cambio de contraseña propia.

- Clase: [Account/Password.php](../../../../app/Livewire/Admin/Account/Password.php).
- Vista: [livewire.admin.account.password](../../../../resources/views/livewire/admin/account/password.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

Exige sesión administrativa activa. Las rutas de cuenta están exceptuadas de permisos de módulos en [CheckPermission](../../../../app/Http/Middleware/CheckPermission.php).

## Funcionamiento paso a paso

1. save valida contraseña actual y nueva contraseña confirmada de 10 a 255 caracteres.
2. Actualiza la contraseña y el remember_token; elimina las otras sesiones de ese administrador conservando la actual.
3. Audita, limpia los tres campos y muestra confirmación.

## Reglas y casos particulares

La ruta exige autenticación; no depende de un permiso de módulo.

## Métodos de referencia

`render()`, `save()`.

## Componentes de la vista

- `<x-form.title />`
- `<x-layout.error />`
- `<x-form.container-sm />`
- `<x-form.text-input />`
- `<x-layout.loader.fullpage />`

[Volver al índice administrativo](../README.md).
