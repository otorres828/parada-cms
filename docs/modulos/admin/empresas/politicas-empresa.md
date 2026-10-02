# PoliticasEmpresa

Lectura de las políticas particulares de una empresa.

- Clase: [Empresas/PoliticasEmpresa.php](../../../../app/Livewire/Admin/Empresas/PoliticasEmpresa.php).
- Vista: [livewire.admin.empresas.politicas](../../../../resources/views/livewire/admin/empresas/politicas.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermission.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

## Funcionamiento paso a paso

1. Recibe el ID y busca la empresa.
2. Entrega su contenido de políticas a la vista.
3. Utiliza empresas/detail en el middleware; no requiere una sección adicional de permisos.

## Reglas y casos particulares

Esta clase no publica un formulario de edición de políticas.

## Métodos de referencia

`mount()`, `render()`.

## Componentes de la vista

- `<x-list.heading />`

[Volver al índice administrativo](../README.md).
