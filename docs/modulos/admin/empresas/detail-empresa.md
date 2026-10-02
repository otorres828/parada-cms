# DetailEmpresa

Consulta de los datos y cuentas de una empresa.

- Clase: [Empresas/DetailEmpresa.php](../../../../app/Livewire/Admin/Empresas/DetailEmpresa.php).
- Vista: [livewire.admin.empresas.detail-empresa](../../../../resources/views/livewire/admin/empresas/detail-empresa.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermission.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
Access::allows('empresas.users', 'list');
~~~

## Funcionamiento paso a paso

1. mount recupera la empresa y calcula empresas.users/list para el enlace a sus usuarios.
2. render presenta la descripción y datos relacionados cargados por findEmpresa.
3. Las políticas se consultan con el mismo permiso empresas/detail.

## Reglas y casos particulares

La pantalla no calcula balances ni retiros.

## Métodos de referencia

`mount()`, `render()`, `findEmpresa()`.

## Componentes de la vista

- `<x-empresas.description />`
- `<x-empresas.datos-bancarios />`
- `<x-form.cancel-button />`
- `<x-layout.loader.fullpage />`
- `<x-list.heading />`

[Volver al índice administrativo](../README.md).
