# DetailTransporte

Consulta la ficha de un transporte de la empresa autenticada y su historial de programaciones.

## Acceso y datos

La ruta `empresas.transportes.detail` requiere el permiso `transportes/detail`. `mount` carga el transporte mediante `findTransporte`, limitado por empresa y tipo de transporte; un transporte ajeno devuelve 404. La ficha muestra placa, modelo, capacidad, estado y amenidades.

## Historial y navegación

`render` consulta las programaciones del transporte y de la empresa, ordenadas por salida descendente, con paginación Bootstrap. Muestra precios, pasajes pagados y pendientes y ventas. Las tasas aparecen solamente cuando la empresa recibe el dinero. El enlace a una programación depende de `programaciones/detail`; Volver depende de `transportes/list`.

Un transporte inactivo mantiene disponible el historial de sus programaciones. El listado incorpora el botón de detalle según `canDetail`.
