# SaveCliente

Edición de la identidad y contacto de un cliente.

- Clase: [Clientes/SaveCliente.php](../../../../app/Livewire/Admin/Clientes/SaveCliente.php).
- Vista: [livewire.admin.clientes.save-cliente](../../../../resources/views/livewire/admin/clientes/save-cliente.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermission.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
Access::authorize('clientes', $this->user_id ? 'edit' : 'add');
~~~

## Funcionamiento paso a paso

1. mount recibe user_id y carga los campos del cliente.
2. validateForm comprueba los datos enviados; save autoriza la acción correspondiente antes de persistir.
3. Guarda datos y auditoría dentro de una transacción y regresa al listado.

## Reglas y casos particulares

Las rutas administrativas publican edición, no alta de clientes. El guardado del teléfono pasa por los casts y eventos del modelo User.

## Validación del formulario

Reglas declaradas en línea. Las condiciones adicionales y las reglas multilínea se consultan en la clase enlazada.

~~~php
'name' => ['required', 'string', 'max:255'],
'lastname' => ['required', 'string', 'max:255'],
'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user_id)],
'telefono' => ['nullable', 'string', 'max:100'],
'status' => ['required', 'in:1,2'],
~~~

## Métodos de referencia

`mount()`, `render()`, `save()`, `editar()`, `validateForm()`, `findUser()`.

## Componentes de la vista

- `<x-list.heading />`
- `<x-form.cancel-button />`
- `<x-layout.error />`
- `<x-form.container-sm />`
- `<x-form.text-input />`
- `<x-form.dropdown />`
- `<x-layout.loader.fullpage />`

[Volver al índice administrativo](../README.md).
