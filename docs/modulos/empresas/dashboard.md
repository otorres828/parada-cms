# Dashboard

`App\Livewire\Empresas\Dashboard` utiliza una vista propia en `livewire.empresas.dashboard`.

El middleware protege la entrada con el permiso dashboard/list. En mount se calculan los permisos para los enlaces a Reservas y Programaciones. No se crean enlaces a detalles todavía no implementados.

## Indicadores

Muestra ventas, tasas, reservas pagadas, pasajes y pendientes; distribución por estado, reservas recientes y próximas salidas. Las consultas siempre reciben la empresa del usuario autenticado. El tipo de transporte se obtiene de su tipo de entidad y no puede seleccionarse en pantalla ni por URL.

Los períodos son hoy, últimos 7 días, últimos 30 días, mes actual y rango personalizado. Los cambios de fechas activan el período personalizado; las fechas inválidas utilizan un valor de respaldo y registran un error de validación.

Las próximas salidas corresponden a los siguientes siete días, independientemente del período comercial. Incluyen disponibilidad por tramo y solo rutas y empresas activas. No se muestra la tarjeta de gestión de empresas ni el botón de registrar empresas.

Los indicadores monetarios mantienen el cálculo del dashboard administrativo. El filtro de pagadas respeta los estados definidos por searchAdmin. Los datos históricos conservan el estado actual de cada reserva.

## Importes según contrato

Ellos reciben muestra el total cobrado y las tasas de servicio. La plataforma recibe muestra el importe de los pasajes menos descuentos, sin tasas. En Pasajes, subtotal se presenta como Total para La plataforma recibe; su Excel empresarial aplica la misma condición. En el dashboard se oculta la tarjeta Tasas para ese contrato y Ventas pagadas resta las tasas del total cobrado.
