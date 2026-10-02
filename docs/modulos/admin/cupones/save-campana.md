# SaveCampana

Configuración y generación inicial de una campaña.

- Clase: [Cupones/SaveCampana.php](../../../../app/Livewire/Admin/Cupones/SaveCampana.php).
- Vista: [livewire.admin.cupones.save-campana](../../../../resources/views/livewire/admin/cupones/save-campana.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermission.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
Access::authorize('cupones', $this->configuracion_cupon_id ? 'edit' : 'add');
~~~

## Funcionamiento paso a paso

1. Carga la campaña en edición; el buscador de empresa devuelve hasta 100 opciones e incorpora la selección actual.
2. Valida modalidad, aplicación por reserva o pasaje, descuento, fechas y cantidad de 1 a 1000; limita porcentajes a 100 y normaliza el código personalizado.
3. Si ya hay cupones, descarta modificaciones de empresa, código, cantidad, tipo de cupón, tipo y ámbito del descuento, monto e inicio.
4. Guarda en transacción y, solo al crear, delega generación aleatoria a CuponService; audita y vuelve al listado.

## Reglas y casos particulares

Los campos bloqueados se descartan en el servidor, no solo se deshabilitan en la vista. Nombre, modalidad, fin y estado no pertenecen a esa lista de campos inmutables.

## Validación del formulario

Reglas declaradas en línea. Las condiciones adicionales y las reglas multilínea se consultan en la clase enlazada.

~~~php
'empresa_id' => ['nullable', 'integer', 'exists:empresas,id'],
'nombre_campana' => ['required', 'string', 'max:255'],
'tipo_cupon' => ['required', 'integer', 'in:1,2'],
'modalidad' => ['required', 'in:GENERAL,PRIMERA_COMPRA,USUARIO_NUEVO'],
'aplica_en' => ['required', 'in:reserva,pasajes'],
'cantidad_generar' => ['required', 'integer', 'min:1', 'max:1000'],
'tipo_descuento' => ['required', 'in:porcentaje,monto_fijo'],
'monto_descuento' => ['required', 'decimal:0,2', 'min:0.01', 'max:999999.99'],
'fecha_inicio' => ['required', 'date'],
'fecha_fin' => ['required', 'date', 'after:fecha_inicio'],
'estatus' => ['required', 'in:0,1,2'],
~~~

## Métodos de referencia

`mount()`, `render()`, `save()`, `editar()`, `validateForm()`, `findConfiguracionCupon()`.

## Componentes de la vista

- `<x-list.heading />`
- `<x-form.cancel-button />`
- `<x-layout.error />`
- `<x-form.container-sm />`
- `<x-form.dropdown />`
- `<x-form.text-input />`
- `<x-layout.loader.fullpage />`

[Volver al índice administrativo](../README.md).
