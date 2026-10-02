# Dashboard

Resumen operativo y financiero por período y transporte.

- Clase: [Dashboard.php](../../../app/Livewire/Admin/Dashboard.php).
- Vista: [livewire.admin.dashboard](../../../resources/views/livewire/admin/dashboard.blade.php).
- Registro de rutas: [admin.php](../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../app/Http/Middleware/CheckPermissionAdmin.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
Access::authorize('dashboard', 'list');
~~~

## Funcionamiento paso a paso

1. Inicializa el período cuando faltan fechas; boot autoriza dashboard/list.
2. Normaliza el rango y calcula ventas, tasas, reservas, pasajes y distribución de estados mediante los modelos.
3. Consulta próximas salidas desde hoy hasta seis días después, independientemente del período histórico, y muestra hasta cinco.
4. Cambiar una fecha pasa a período personalizado; una fecha inválida agrega un error y usa un valor de respaldo.

## Filtros y estado en URL

- `periodo`
- `date_from`
- `date_to`
- `tipo_transporte`

## Métodos de referencia

`mount()`, `render()`, `updatedPeriodo()`, `updatedDateFrom()`, `updatedDateTo()`, `boot()`, `applyPeriod()`, `parseDate()`.

## Componentes de la vista

- `<x-list.disponibilidad-tramos />`

[Volver al índice administrativo](README.md).
