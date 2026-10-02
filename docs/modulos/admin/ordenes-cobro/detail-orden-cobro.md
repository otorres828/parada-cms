# DetailOrdenCobro

Revisión de una orden y consulta de las reservas incluidas.

- Clase: [OrdenesCobro/DetailOrdenCobro.php](../../../../app/Livewire/Admin/OrdenesCobro/DetailOrdenCobro.php).
- Vista: [livewire.admin.ordenes-cobro.detail-orden-cobro](../../../../resources/views/livewire/admin/ordenes-cobro/detail-orden-cobro.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermissionAdmin.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
Access::authorize('ordenes-cobro', 'review');
Access::authorize('ordenes-cobro', 'download');
~~~

## Funcionamiento paso a paso

1. mount conserva ID y permisos review/download; render recupera detalle y conversiones.
2. aprobar exige review y delega la transición a OrdenCobroService, luego audita y muestra confirmación.
3. rechazar exige review y motivo obligatorio de hasta 2000 caracteres; delega, audita y limpia el motivo.
4. exportExcel exige download y exporta el snapshot reservas_incluidas y sus conversiones mediante ReservasOrdenCobroExport.

## Reglas y casos particulares

La búsqueda visual de reservas pertenece al componente de tabla; esta clase no recibe un search para limitar el Excel.

## Validación del formulario

Reglas declaradas en línea. Las condiciones adicionales y las reglas multilínea se consultan en la clase enlazada.

~~~php
'motivo' => ['required', 'string', 'max:2000'],
~~~

## Métodos de referencia

`mount()`, `render()`, `aprobar()`, `rechazar()`, `exportExcel()`.

## Componentes de la vista

- `<x-list.heading />`
- `<x-form.cancel-button />`
- `<x-layout.error />`
- `<x-layout.loader.fullpage />`
- `<x-money.dual />`

[Volver al índice administrativo](../README.md).
