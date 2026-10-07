# Correo de reserva pagada

`Reserva::sendMailReserva()` encola `ReservaPagadaNotification` cuando la reserva está pagada y su dueño tiene un correo válido. En reservas web utiliza el correo del usuario; en taquilla utiliza `comprador_json.email`. Sin correo válido se omite el envío y la venta continúa.

`Reserva::booted()` escucha `saved` y llama al método únicamente cuando `estado_pago` cambia a pagada o se crea una reserva pagada. Guardar otros campos o repetir una confirmación no crea otra notificación. Los servicios de pago y reprogramación no realizan llamadas manuales. En `saved`, `isDirty('estado_pago')` comprueba el cambio de este guardado antes de que Eloquent sincronice los valores originales.

La notificación se despacha después del commit: una transacción revertida no envía el correo. El trabajador vuelve a comprobar que la reserva siga pagada, por lo que no entrega pasajes de una reserva ya cancelada, reembolsada o reprogramada.

El mensaje incluye comprador, empresa, ruta, fechas y horas del tramo, transporte, resumen en USD y Bs con el cambio histórico de la reserva y los pasajes con sus QR. Adjunta un recibo PDF generado con Dompdf; los QR PNG se incrustan en el correo para no depender de recursos externos.

Debe estar configurado el correo de Laravel y ejecutarse un trabajador de la cola, por ejemplo `php artisan queue:work`. La generación del PDF y los QR requiere las extensiones PHP GD y DOM. Los trabajos tienen tres intentos y sus fallos se reportan al registro de errores. El método no impide un reenvío solicitado explícitamente; la deduplicación automática corresponde al cambio de estado detectado por el modelo. Las actualizaciones masivas mediante Builder no disparan eventos Eloquent; para confirmar pagos debe guardarse la instancia de Reserva.

El correo usa `emails.reserva-pagada` con la plantilla Markdown nativa de Laravel (`mail::message`, `mail::panel` y `mail::table`), estilos en línea y ancho adaptable. El recibo PDF utiliza una vista independiente, `emails.reserva-pagada-pdf`, para conservar su formato de impresión.

En el detalle de reservas de Admin y Empresas, el botón Reenviar correo solicita confirmación mediante SweetAlert. La acción exige permiso de detalle, vuelve a consultar la reserva (limitada a la empresa autenticada en el panel empresarial) y admite únicamente reservas pagadas con correo válido. La alerta de éxito confirma que el envío quedó encolado, no su entrega.
