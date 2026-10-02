# SaveExoneracionTasaServicio

Alta o edición de períodos exonerados para una empresa.

- Clase: [ExoneracionesTasaServicio/SaveExoneracionTasaServicio.php](../../../../app/Livewire/Admin/ExoneracionesTasaServicio/SaveExoneracionTasaServicio.php).
- Vista: [livewire.admin.exoneraciones-tasa-servicio.save-exoneracion-tasa-servicio](../../../../resources/views/livewire/admin/exoneraciones-tasa-servicio/save-exoneracion-tasa-servicio.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermission.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
Access::authorize( 'exoneraciones-tasa-servicio', $this->exoneracion_tasa_servicio_id ? 'edit' : 'add', );
~~~

## Funcionamiento paso a paso

1. Carga el registro cuando hay ID y presenta empresas activas.
2. Valida empresa, inicio, final opcional posterior o igual, motivo y estatus 1 o 2.
3. Normaliza fin vacío a null, bloquea el registro al editar y delega validación de solapamientos al modelo.
4. Guarda y audita dentro de la transacción, luego vuelve al listado.

## Validación del formulario

Reglas declaradas en línea. Las condiciones adicionales y las reglas multilínea se consultan en la clase enlazada.

~~~php
'empresa_id' => ['required', 'integer', 'exists:empresas,id'],
'fecha_desde' => ['required', 'date'],
'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
'motivo' => ['required', 'string', 'max:255'],
'estatus' => ['required', 'integer', 'in:1,2'],
~~~

## Métodos de referencia

`mount()`, `render()`, `save()`, `editar()`.

## Componentes de la vista

- `<x-list.heading />`
- `<x-form.container-sm />`
- `<x-form.dropdown />`
- `<x-form.text-input />`
- `<x-form.cancel-button />`
- `<x-layout.error />`
- `<x-layout.loader.fullpage />`

[Volver al índice administrativo](../README.md).
