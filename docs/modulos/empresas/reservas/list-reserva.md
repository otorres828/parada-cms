# ListReserva

Listado del panel de Empresas. Clase `app/Livewire/Empresas/Reservas/ListReserva.php` y vista `livewire.empresas.reservas.list-reserva`.

## Acceso y alcance

El middleware exige el permiso `reservas/list`. En `mount` se resuelven los permisos de presentación con `PermissionsEmpresa`. Las acciones de descarga o escritura vuelven a autorizarse en el servidor.

La consulta obtiene `empresa_id` del usuario autenticado en el guard `empresa` en cada ejecución. No existe un selector ni una propiedad pública para elegir otra empresa. Los listados utilizan `searchAdmin`; los reportes utilizan consultas agregadas del modelo Reserva con el mismo alcance obligatorio.

## Funcionamiento

Búsqueda, rango de compra y estado de pago. Descarga Excel con permiso download, sin columna Empresa.

Los cambios de filtros reinician la página. La paginación limita cada página a 100 registros. Los módulos con fechas inician con la última semana y utilizan la normalización de fechas del modelo.

La vista conserva el encabezado, filtros y paginación del panel; la tabla está definida en su propia vista de Empresas, independiente de Admin. Solo se reutilizan elementos básicos de presentación como buscadores, botones, montos y contenedores. En Empresas no se muestra la columna Empresa ni se generan enlaces a rutas de detalle o edición que todavía no existen. Los formularios y detalles se implementarán por separado.

## Importes según contrato

Ellos reciben muestra el total cobrado y las tasas de servicio. La plataforma recibe muestra el importe de los pasajes menos descuentos, sin tasas. En Pasajes, subtotal se presenta como Total para La plataforma recibe; su Excel empresarial aplica la misma condición. En el dashboard se oculta la tarjeta Tasas para ese contrato y Ventas pagadas resta las tasas del total cobrado.
