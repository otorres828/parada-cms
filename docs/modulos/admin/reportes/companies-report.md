# CompaniesReport

Reporte agregado de ventas por empresa.

- Clase: [Reportes/CompaniesReport.php](../../../../app/Livewire/Admin/Reportes/CompaniesReport.php).
- Vista: [livewire.admin.reportes.companies-report](../../../../resources/views/livewire/admin/reportes/companies-report.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermissionAdmin.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
Access::authorize('reportes', 'list-companies');
~~~

## Funcionamiento paso a paso

1. mount inicializa fechas predeterminadas.
2. query usa Reserva::companiesReport con fechas y tipo de transporte; render pagina resultados.
3. updated reinicia paginación.
4. export autoriza reportes/list-companies y valida el rango antes de descargar Excel.

## Filtros y estado en URL

- `tipo_transporte`
- `date_from`
- `date_to`
- `per_page`

## Validación del formulario

Reglas declaradas en línea. Las condiciones adicionales y las reglas multilínea se consultan en la clase enlazada.

~~~php
'tipo_transporte' => 'nullable|in:autobus,carro',
'date_from' => 'required|date_format:Y-m-d|after_or_equal:'.self::getMinFilterDate().'|before_or_equal:'.self::getMaxFilterDate(),
'date_to' => 'required|date_format:Y-m-d|after_or_equal:date_from|before_or_equal:'.self::getMaxFilterDate(),
'per_page' => 'integer|in:10,25,50,100',
~~~

## Métodos de referencia

`mount()`, `render()`, `updated()`, `query()`, `export()`.

## Componentes de la vista

- `<x-list.heading />`
- `<x-layout.error />`
- `<x-form.text-input />`
- `<x-list.table />`
- `<x-layout.loader.fullpage />`
- `<x-money.dual />`

[Volver al índice administrativo](../README.md).
