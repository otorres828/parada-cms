# SaveTerminal

Alta o edición de ubicación y coordenadas de una terminal.

- Clase: [Terminales/SaveTerminal.php](../../../../app/Livewire/Admin/Terminales/SaveTerminal.php).
- Vista: [livewire.admin.terminales.save-terminal](../../../../resources/views/livewire/admin/terminales/save-terminal.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermissionAdmin.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
Access::authorize('terminales', $this->terminal_id ? 'edit' : 'add');
~~~

## Funcionamiento paso a paso

1. mount carga estados y datos de la terminal si corresponde.
2. save valida estado existente, nombre, dirección, latitud entre -90 y 90 y longitud entre -180 y 180.
3. Actualiza campos, guarda y audita en transacción.
4. Regresa al listado; el mapa y la búsqueda de dirección se manejan en el componente de la vista.

## Reglas y casos particulares

El servidor valida coordenadas numéricas. La regla actual de estatus admite 0/1.

## Validación del formulario

Reglas declaradas en línea. Las condiciones adicionales y las reglas multilínea se consultan en la clase enlazada.

~~~php
'estado_id' => ['required', 'integer', 'exists:estados,id'],
'nombre' => ['required', 'string', 'max:255'],
'direccion' => ['required', 'string', 'max:2000'],
'latitud' => ['required', 'numeric', 'between:-90,90'],
'longitud' => ['required', 'numeric', 'between:-180,180'],
'estatus' => ['required', 'in:0,1'],
~~~

## Métodos de referencia

`mount()`, `render()`, `save()`, `editar()`, `validateForm()`, `findTerminal()`.

## Componentes de la vista

- `<x-list.heading />`
- `<x-form.cancel-button />`
- `<x-layout.error />`
- `<x-form.dropdown />`
- `<x-form.text-input />`
- `<x-form.textarea />`
- `<x-form.location-map />`
- `<x-layout.loader.fullpage />`

[Volver al índice administrativo](../README.md).
