# SavePregunta

Redacción y edición de un artículo de ayuda.

- Clase: [PreguntasFrecuentes/SavePregunta.php](../../../../app/Livewire/Admin/PreguntasFrecuentes/SavePregunta.php).
- Vista: [livewire.admin.preguntas-frecuentes.save](../../../../resources/views/livewire/admin/preguntas-frecuentes/save.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermissionAdmin.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
Access::authorize('preguntas-frecuentes', $this->pregunta_id ? 'edit' : 'add');
~~~

## Funcionamiento paso a paso

1. Carga categorías y el artículo si existe.
2. Normaliza slug y valida unicidad, categoría existente no eliminada, resumen, respuesta, palabras clave, destacada, orden y estado 1/2.
3. Guarda en transacción, bloqueando el artículo en edición, y registra auditoría.
4. Regresa al listado con un mensaje de éxito.

## Reglas y casos particulares

La respuesta enriquecida admite hasta 100000 caracteres y el resumen hasta 500. Una categoría inactiva no se excluye por la regla exists; la condición es que no esté eliminada.

## Validación del formulario

Reglas declaradas en línea. Las condiciones adicionales y las reglas multilínea se consultan en la clase enlazada.

~~~php
'pregunta' => ['required', 'string', 'max:255'],
'resumen' => ['required', 'string', 'max:500'],
'respuesta' => ['required', 'string', 'max:100000'],
'palabras_clave' => ['nullable', 'string', 'max:1000'],
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
- `<x-form.rich-text-editor />`
- `<x-layout.error />`
- `<x-layout.loader.fullpage />`

[Volver al índice administrativo](../README.md).
