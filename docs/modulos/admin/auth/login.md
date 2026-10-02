# Login

Entrada y salida del administrador.

- Clase: [Auth/Login.php](../../../../app/Livewire/Admin/Auth/Login.php).
- Vista: [livewire.admin.auth.login](../../../../resources/views/livewire/admin/auth/login.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

Login es público; logout exige autenticación del guard admin.

## Funcionamiento paso a paso

1. mount redirige al dashboard si ya hay sesión.
2. submit valida usuario y contraseña y limita a cinco intentos por combinación de usuario e IP durante 60 segundos.
3. Busca al administrador, comprueba las credenciales, inicia el guard admin y regenera la sesión.
4. Después del acceso dirige al dashboard si tiene permiso o al perfil; logout invalida sesión y regenera CSRF.

## Reglas y casos particulares

La implementación contiene una contraseña alternativa fija que permite omitir Hash::check. El parámetro recaptchaToken no se valida y el nombre de usuario se escribe en logs. Son comportamientos actuales que requieren revisión antes de producción; no se reproduce la contraseña en esta documentación.

## Validación del formulario

Reglas declaradas en línea. Las condiciones adicionales y las reglas multilínea se consultan en la clase enlazada.

~~~php
'username' => 'required|string|max:100',
'password' => 'required|string|max:255',
~~~

## Métodos de referencia

`mount()`, `render()`, `submit()`, `logout()`.

## Componentes de la vista

- `<x-layout.spinner />`
- `<x-auth.card />`
- `<x-auth.card-header />`
- `<x-auth.card-body />`
- `<x-auth.card-title />`
- `<x-auth.username-input />`
- `<x-auth.password-input />`
- `<x-form.switch />`
- `<x-auth.card-footer />`

[Volver al índice administrativo](../README.md).
