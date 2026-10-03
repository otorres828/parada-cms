# PagoTaquillaService

`validarPago` valida un importe de la cotización sin persistir. `registrarPagos` guarda el conjunto dentro de la transacción de la venta: autoriza, comprueba reserva editable, empresa y salida, recalcula importes y exige que la suma coincida con el total.

Cada fila en pagos_reservas representa un cobro: tipo_pago (1 transferencia, 2 efectivo, 3 pago móvil, 4 tarjeta), monto_recibido y total siempre en dólares, referencia y cuenta receptora. La reserva expone pagos() HasMany; pago() se conserva como relación al primer cobro para compatibilidad. La confirmación general compara la suma de todos los pagos. Los reembolsos se limitan a una solicitud por reserva, aunque tenga varios cobros.

La transferencia no está en el formulario de taquilla. Pago móvil requiere una cuenta activa propia de tipo pago móvil; tarjeta no requiere cuenta receptora. El selector muestra el tipo y número de documento del titular de la cuenta receptora; no se solicitan documentos del pagador. Referencias no se reutilizan. Efectivo genera referencia automáticamente. La moneda solo se utiliza en la cotización, no es una columna de pagos_reservas. Bolívares se convierten con valor_usd de la tasa fijada en la reserva y se redondean a dos decimales por pago.

El registro completo de taquilla requiere permiso reservas/confirm para todos los métodos. Después de verificar la suma, se confirma la reserva dentro de la misma transacción: queda pagada y sus QR disponibles, incluso si incluye pago móvil. confirmar exige permiso y comprueba el total conjunto. rechazar marca fallida y libera cupo; conserva los cobros como historial, sin ejecutar devoluciones bancarias.

El método registrar de un solo pago se mantiene para compatibilidad del flujo previo. La nueva pantalla utiliza exclusivamente registrarPagos a través de ReservaTaquillaService::registrar.
