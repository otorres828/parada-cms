# SaveAmenidad

Registro y edición de nombre e icono de una amenidad.

- Clase: [Amenidades/SaveAmenidad.php](../../../../app/Livewire/Admin/Amenidades/SaveAmenidad.php).
- Vista: [livewire.admin.amenidades.save-amenidad](../../../../resources/views/livewire/admin/amenidades/save-amenidad.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermission.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
Access::authorize('amenidades', $this->amenidad_id ? 'edit' : 'add');
~~~

## Funcionamiento paso a paso

1. mount conserva el ID bloqueado y carga los campos si se está editando.
2. save autoriza add o edit y validateForm exige nombre, icono Bootstrap con patrón bi- y estado permitido.
3. Crea o actualiza el registro dentro de una transacción, audita y redirige al listado.

## Reglas y casos particulares

La validación actual admite estatus 0 y 1, mientras el listado alterna 1 y 2. Se documenta esta diferencia sin modificar la implementación.

## Validación del formulario

Reglas declaradas en línea. Las condiciones adicionales y las reglas multilínea se consultan en la clase enlazada.

~~~php
'nombre' => ['required', 'string', 'max:255'],
'icono' => ['required', 'string', 'max:64', 'regex:/^bi-[a-z0-9-]+$/'],
'estatus' => ['required', 'in:0,1'],
~~~

## Métodos de referencia

`mount()`, `render()`, `save()`, `editar()`, `validateForm()`, `findAmenidad()`.

## Componentes de la vista

- `<x-list.heading />`
- `<x-form.cancel-button />`
- `<x-layout.error />`
- `<x-form.container-sm />`
- `<x-form.text-input />`
- `<x-form.dropdown />`
- `<x-layout.loader.fullpage />`

[Volver al índice administrativo](../README.md).
