# SaveCategoria

Alta o edición de una categoría y su imagen.

- Clase: [PreguntasFrecuentes/SaveCategoria.php](../../../../app/Livewire/Admin/PreguntasFrecuentes/SaveCategoria.php).
- Vista: [livewire.admin.preguntas-frecuentes.save-categoria](../../../../resources/views/livewire/admin/preguntas-frecuentes/save-categoria.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermission.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
Access::authorize('preguntas-frecuentes', $this->categoria_id ? 'edit' : 'add');
~~~

## Funcionamiento paso a paso

1. Carga los valores anteriores al editar y normaliza slug desde slug o nombre.
2. Valida slug único, descripción hasta 500, imagen opcional hasta 3 MB, orden y estatus 1 o 2.
3. Si hay imagen nueva, la almacena en centro-ayuda/categorias del disco público; guarda y audita en transacción.
4. Después de guardar elimina la imagen anterior reemplazada y regresa al listado de categorías.

## Reglas y casos particulares

La imagen se almacena antes de la transacción SQL. La clase no contiene compensación que borre esa imagen nueva si falla el guardado.

## Validación del formulario

Reglas declaradas en línea. Las condiciones adicionales y las reglas multilínea se consultan en la clase enlazada.

~~~php
'nombre' => ['required', 'string', 'max:255'],
'descripcion' => ['nullable', 'string', 'max:500'],
'icono' => ['nullable', 'string', 'max:100'],
'imagen' => ['nullable', 'image', 'max:3072'],
'destacada' => ['required', 'boolean'],
'orden' => ['required', 'integer', 'min:0', 'max:99999'],
'estatus' => ['required', 'integer', 'in:1,2'],
~~~

## Métodos de referencia

`mount()`, `render()`, `save()`, `editar()`, `validateForm()`.

## Componentes de la vista

- `<x-list.heading />`
- `<x-form.cancel-button />`
- `<x-form.text-input />`
- `<x-form.dropdown />`
- `<x-layout.error />`
- `<x-layout.loader.fullpage />`

[Volver al índice administrativo](../README.md).
