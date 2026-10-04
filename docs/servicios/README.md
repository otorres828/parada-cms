# Entidades y servicios de negocio

Esta carpeta documenta las entidades principales y los servicios que coordinan sus cambios. Los modelos conservan relaciones, consultas y reglas propias de la entidad; los servicios coordinan operaciones que involucran varias entidades o una transición completa de negocio.

## Flujos documentados

- [Reservas y checkout](logica-checkout.md): creación o reinicio de la reserva, pasajeros, cupones, tasas y pago.
- [Viajeros](servicio-viajero.md): libreta del cliente, snapshot del pasaje, cifrado, hashes y eliminación.
- [Datos personales cifrados](logica-datos-personales-cifrados.md): llaves, casts cifrados e índices HMAC.
- [Cupones](servicio-cupon.md): generación, aplicación, distribución y liberación.
- [Tasas de servicio](servicio-tasas-servicio.md): exoneraciones, snapshots y cálculo por pasaje.
- [Pagos de reservas](servicio-pago-reserva.md): reporte, confirmación y pago fallido.
- [Órdenes de cobro](servicio-orden-cobro.md): emisión, revisión y bloqueo por cobranza.
- [Reembolsos](servicio-reembolso.md): creación y resolución desde la empresa.
- [Tipo de cambio](servicio-tipo-cambio.md): actualización desde la fuente configurada.
- [ReservaService](servicio-reserva.md): métodos, reinicio, propiedad y transacciones de la reserva.
- [Access](servicio-access.md): autorización administrativa.
- [Access de Empresas](servicio-access-empresa.md): permisos empresariales, usuarios administradores y catálogo inicial.
- [Audit](servicio-audit.md): registro explícito de acciones y exclusión de claves sensibles.

## Convención de nombres

- `servicio-<nombre>.md`: una clase de `app/Services` por archivo, con su nombre en slug y sin el sufijo `Service`.
- `logica-<tema>.md`: entidades, reglas compartidas o flujos entre servicios.
- `README.md`: índice de navegación.

La documentación registra la implementación actual. Las diferencias respecto a reglas comerciales previstas se indican en el documento correspondiente; no se presentan como funcionalidades ya implementadas.

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

- [Reservas de taquilla](servicio-reserva-taquilla.md)
- [Pagos de taquilla](servicio-pago-taquilla.md)

- [Horarios por tramo de programación](logica-horarios-tramos.md)

- [Servicio de rutas de Empresas](servicio-viaje.md)

- [Servicio de programaciones](servicio-programacion.md).
