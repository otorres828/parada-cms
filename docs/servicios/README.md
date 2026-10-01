# Entidades y servicios de negocio

Esta carpeta documenta las entidades principales y los servicios que coordinan sus cambios. Los modelos conservan relaciones, consultas y reglas propias de la entidad; los servicios coordinan operaciones que involucran varias entidades o una transición completa de negocio.

## Flujos documentados

- [Reservas y checkout](reservas-y-checkout.md): creación o reinicio de la reserva, pasajeros, cupones, tasas y pago.
- [Viajeros](viajeros.md): libreta del cliente, snapshot del pasaje, cifrado, hashes y eliminación.
- [Datos personales cifrados](datos-personales-cifrados.md): llaves, casts cifrados e índices HMAC.
- [Cupones](cupones.md): generación, aplicación, distribución y liberación.
- [Tasas de servicio](tasas-servicio.md): exoneraciones, snapshots y cálculo por pasaje.
- [Pagos de reservas](pagos-reservas.md): reporte, confirmación y pago fallido.
- [Órdenes de cobro](ordenes-cobro.md): emisión, revisión y bloqueo por cobranza.
- [Reembolsos](reembolsos.md): creación y resolución desde la empresa.
- [Tipo de cambio](tipo-cambio.md): actualización desde la fuente configurada.
- [Acceso y auditoría](acceso-y-auditoria.md): permisos y registro de acciones administrativas.

## Servicios actuales

| Servicio | Responsabilidad |
| --- | --- |
| `ReservaService` | Crea o reinicia reservas nuevas, incorpora o retira pasajeros, prepara el resumen y cancela reservas editables. |
| `ViajeroService` | Crea viajeros y decide entre eliminación física, eliminación lógica o rechazo por una reserva abierta. |
| `CuponService` | Genera códigos, aplica, recalcula, retira y libera cupones. |
| `TasasServicioService` | Detecta exoneraciones y calcula la tasa de cada pasaje y el total de la reserva. |
| `PagoReservaService` | Registra el pago reportado, pasa la reserva a pendiente, confirma el pago o lo marca como fallido. |
| `OrdenCobroService` | Emite órdenes por tasas, recibe comprobantes, aprueba o rechaza y controla bloqueos por cobranza. |
| `Empresa\ReembolsoService` | Crea y resuelve reembolsos gestionados por la empresa. |
| `TipoCambioService` | Consulta la fuente configurada y registra las tasas USD y EUR válidas. |
| `Admin\Access` | Comprueba permisos administrativos activos. |
| `Admin\Audit` | Registra acciones administrativas omitiendo datos sensibles conocidos. |
