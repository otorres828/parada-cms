# Módulos del panel de empresas

Esta carpeta documenta los módulos del panel de agencias y conductores a medida que se implementan.

La base de autorización y el catálogo inicial se explican en [Access de Empresas](../../servicios/servicio-access-empresa.md).

El orden previsto del panel es Dashboard, Administración, Operación de viajes, Ventas y finanzas, Cobranza, Promociones y Reportes. El catálogo de permisos ya contempla sus 15 secciones; esto no implica que todas las pantallas estén implementadas.

La documentación se incorporará a medida que se implemente cada módulo. Cada archivo deberá explicar su objetivo, usuarios autorizados, acciones, estados, reglas y relación con los servicios de negocio.

## Menú lateral

`resources/views/components/layout/sidebar.blade.php` selecciona los componentes mediante `request()->is`: `/admin` y sus subrutas utilizan el menú administrativo; `/empresa` y sus subrutas utilizan `administration-menu-empresa`. Ambos comparten `logo`, que selecciona internamente el enlace al dashboard según el panel.

`App\View\Components\Layout\Sidebar\AdministrationMenuEmpresa` obtiene el usuario del guard `empresa`, comprueba que esté activo y resuelve los permisos en lote. La vista reutiliza `sidebar-li`, con el mismo patrón de grupos, enlaces y opciones activas de Admin. Mi cuenta no exige permisos de módulo.

Los grupos siguen el orden del catálogo empresarial. Una opción solo aparece si el usuario tiene permiso y su ruta está registrada; al implementar cada módulo se debe mantener sincronizado su nombre de ruta en la vista del menú. La clase solo asigna los booleanos de permisos, siguiendo AdministrationMenuAdmin; las comprobaciones de existencia de rutas y la generación de enlaces permanecen en Blade. Los grupos sin opciones visibles no se muestran.
