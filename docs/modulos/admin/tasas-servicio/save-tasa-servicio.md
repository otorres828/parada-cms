# SaveTasaServicio

Configuración de un rango y su cálculo fijo o porcentual.

- Clase: [TasasServicio/SaveTasaServicio.php](../../../../app/Livewire/Admin/TasasServicio/SaveTasaServicio.php).
- Vista: [livewire.admin.tasas-servicio.save-tasa-servicio](../../../../resources/views/livewire/admin/tasas-servicio/save-tasa-servicio.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermission.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
Access::authorize('tasas-servicio', $this->tasa_servicio_id ? 'edit' : 'add');
~~~

## Funcionamiento paso a paso

1. Carga valores anteriores si hay ID.
2. Valida límites monetarios, máximo mayor o igual al mínimo y cantidad; para porcentaje impone máximo 100.
3. Normaliza máximo vacío a null y bloquea el grupo administración al guardar la tasa.
4. Audita y redirige al listado.

## Reglas y casos particulares

La regla actual de estatus admite 0/1, mientras el listado alterna 1/2. El documento refleja el código actual.

## Validación del formulario

Reglas declaradas en línea. Las condiciones adicionales y las reglas multilínea se consultan en la clase enlazada.

~~~php
'tipo_servicio' => 'required|in:1,2',
'monto_minimo' => 'required|decimal:0,2|min:0|max:9999999999.99',
'monto_maximo' => 'nullable|decimal:0,2|gte:monto_minimo|max:9999999999.99',
'cantidad' => 'required|decimal:0,2|min:0|max:'.((int) $this->tipo_servicio === 2 ? '100' : '9999999999.99'),
'estatus' => 'required|in:0,1',
~~~

## Métodos de referencia

`mount()`, `render()`, `save()`, `editar()`.

## Componentes de la vista

- `<x-list.heading />`
- `<x-form.container-sm />`
- `<x-form.dropdown />`
- `<x-form.text-input />`
- `<x-form.cancel-button />`
- `<x-layout.loader.fullpage />`

[Volver al índice administrativo](../README.md).
