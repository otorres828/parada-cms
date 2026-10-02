# SaveEmpresa

Alta o edición comercial de agencias y conductores.

- Clase: [Empresas/SaveEmpresa.php](../../../../app/Livewire/Admin/Empresas/SaveEmpresa.php).
- Vista: [livewire.admin.empresas.save-empresa](../../../../resources/views/livewire/admin/empresas/save-empresa.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermission.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
Access::authorize('empresas', $this->empresa_id ? 'edit' : 'add');
~~~

## Funcionamiento paso a paso

1. Carga datos en edición y valida tipo de entidad, contacto, contrato y horario.
2. Para contrato donde la empresa recibe el pago exige días de corte y vencimiento del 1 al 7 y distintos entre sí.
3. En el otro contrato guarda esos días como nulos; conserva las horas configuradas.
4. Impide activar una empresa con marca de bloqueo por cobranza, guarda y audita dentro de una transacción y regresa al listado.

## Reglas y casos particulares

El formulario no administra cuentas bancarias ni políticas: su fuente actual contiene identidad, contrato, cobranza y estatus. La regla de estatus del formulario admite 0 y 1.

## Validación del formulario

Reglas declaradas en línea. Las condiciones adicionales y las reglas multilínea se consultan en la clase enlazada.

~~~php
'tipo_entidad' => ['required', 'in:agencia_autobus,conductor_carro'],
'nombre' => ['required', 'string', 'max:255'],
'rif' => ['required', 'string', 'max:255'],
'telefono' => ['required', 'string', 'max:255'],
'email' => ['required', 'email', 'max:255'],
'tipo_contrato' => ['required', 'integer', 'in:1,2'],
'dia_corte' => ['nullable', 'required_if:tipo_contrato,1', 'integer', 'between:1,7'],
'dia_vencimiento' => ['nullable', 'required_if:tipo_contrato,1', 'integer', 'between:1,7', 'different:dia_corte'],
'hora_corte' => ['required', 'date_format:H:i'],
'hora_vencimiento' => ['required', 'date_format:H:i'],
'estatus' => ['required', 'in:0,1'],
~~~

## Métodos de referencia

`mount()`, `render()`, `save()`, `editar()`, `validateForm()`, `findEmpresa()`.

## Componentes de la vista

- `<x-list.heading />`
- `<x-form.cancel-button />`
- `<x-layout.error />`
- `<x-form.container-sm />`
- `<x-form.text-input />`
- `<x-form.dropdown />`
- `<x-layout.loader.fullpage />`

[Volver al índice administrativo](../README.md).
