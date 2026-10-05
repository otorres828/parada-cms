# ListReprogramacion

Lista exclusivamente reservas de la empresa que tienen reprogramacion_id, con referencia a la reserva anterior. Incluye búsqueda, filtros por fecha, ordenación y paginación. El buscador, el rango compacto de fechas y Nuevo registro comparten una fila. No tiene filtro de estado. Nuevo registro requiere reprogramaciones/add; el enlace al detalle reutiliza reservas/detail. No ofrece edición ni eliminación.

Consume una sola vez el flash empresas_reprogramacion_success del alta.
