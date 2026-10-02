# DetailCliente

Consulta del cliente, sus viajeros y reservas.

- Clase: [Clientes/DetailCliente.php](../../../../app/Livewire/Admin/Clientes/DetailCliente.php).
- Vista: [livewire.admin.clientes.detail-cliente](../../../../resources/views/livewire/admin/clientes/detail-cliente.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermission.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
Access::allows('reservas', 'detail');
~~~

## Funcionamiento paso a paso

1. mount consulta clientes mediante User::findAdminDetail y calcula permiso para enlazar reservas.
2. render usa Reserva::searchDetailClient para la tabla de compras.
3. Pagina de 1 a 100 registros por solicitud y reinicia la página cuando cambia per_page.

## Reglas y casos particulares

Los viajeros son la libreta actual; los datos históricos de los boletos pertenecen a Pasaje.

## Métodos de referencia

`mount()`, `render()`, `findUser()`, `updatedPerPage()`.

## Componentes de la vista

- `<x-clientes.description />`
- `<x-clientes.viajeros-table />`
- `<x-clientes.reservas-table />`
- `<x-form.cancel-button />`
- `<x-layout.loader.fullpage />`
- `<x-list.heading />`

[Volver al índice administrativo](../README.md).
