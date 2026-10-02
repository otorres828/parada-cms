# ExchangeRates

Historial y actualización de tasas de cambio.

- Clase: [Reportes/ExchangeRates.php](../../../../app/Livewire/Admin/Reportes/ExchangeRates.php).
- Vista: [livewire.admin.reportes.exchange-rates](../../../../resources/views/livewire/admin/reportes/exchange-rates.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermission.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
Access::allows('reportes', 'update-exchange-rates');
Access::authorize('reportes', 'update-exchange-rates');
~~~

## Funcionamiento paso a paso

1. mount consulta permiso reportes/update-exchange-rates.
2. render pagina el historial y presenta la tasa vigente.
3. updateRates autoriza nuevamente y ejecuta el comando de actualización.
4. Si falla, muestra el resultado como error; si funciona reinicia página y confirma.

## Reglas y casos particulares

La consulta al proveedor y validación de USD/EUR corresponden a TipoCambioService.

## Filtros y estado en URL

- `per_page`

## Métodos de referencia

`mount()`, `render()`, `updateRates()`.

## Componentes de la vista

- `<x-list.heading />`
- `<x-list.table />`
- `<x-layout.loader.fullpage />`

[Volver al índice administrativo](../README.md).
