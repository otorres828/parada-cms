# Módulos del panel de empresas

Esta carpeta documenta los módulos del panel de agencias y conductores a medida que se implementan.

La base de autorización y el catálogo inicial se explican en [Access de Empresas](../../servicios/servicio-access-empresa.md).

El orden previsto del panel es Dashboard, Administración, Operación de viajes, Ventas y finanzas, Cobranza, Promociones y Reportes. El catálogo de permisos ya contempla sus 15 secciones; esto no implica que todas las pantallas estén implementadas.

La documentación se incorporará a medida que se implemente cada módulo. Cada archivo deberá explicar su objetivo, usuarios autorizados, acciones, estados, reglas y relación con los servicios de negocio.

## Menú lateral

`resources/views/components/layout/sidebar.blade.php` selecciona los componentes mediante `request()->is`: `/admin` y sus subrutas utilizan el menú administrativo; `/empresa` y sus subrutas utilizan `administration-menu-empresa`. Ambos comparten `logo`, que selecciona internamente el enlace al dashboard según el panel.

`App\View\Components\Layout\Sidebar\AdministrationMenuEmpresa` obtiene el usuario del guard `empresa`, comprueba que esté activo y resuelve los permisos en lote. La vista reutiliza `sidebar-li`, con el mismo patrón de grupos, enlaces y opciones activas de Admin. Mi cuenta no exige permisos de módulo.

Los grupos siguen el orden del catálogo empresarial. La clase asigna los booleanos de permisos, igual que AdministrationMenuAdmin, y Blade utiliza condiciones por permiso y llamadas directas a route(). Todos los enlaces del menú tienen rutas Livewire y permisos de entrada registrados. Los módulos no incluidos en el índice de listados implementados conservan su pantalla inicial. El listado existente de reembolsos queda limitado a la empresa autenticada; sus acciones posteriores todavía no están habilitadas.

## Listados implementados

- [ListTransporte](transportes/list-transporte.md)
- [ListViaje](viajes/list-viaje.md)
- [ListProgramacion](programaciones/list-programacion.md)
- [ListReserva](reservas/list-reserva.md)
- [ListPasaje](pasajes/list-pasaje.md)
- [ListCampana](cupones/list-campana.md)
- [SalesReport](reportes/sales-report.md)
- [RoutesReport](reportes/routes-report.md)

- [Dashboard](dashboard.md)

- [NUEVA RESERVA](reservas/save-reserva.md)

- [Datos Bancarios: listado](datos-bancarios/list-dato-bancario.md)
- [Datos Bancarios: formulario](datos-bancarios/save-dato-bancario.md)
