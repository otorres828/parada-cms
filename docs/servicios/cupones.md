# CuponService

## Objetivo

Administra la generación, aplicación, recálculo y liberación de cupones. La campaña define si el descuento es fijo o porcentual, si aplica a toda la reserva o individualmente a cada pasaje y qué modalidad de clientes puede utilizarlo.

## Operaciones

- `crearCuponesAleatorios()` genera una sola tanda de códigos únicos para campañas aleatorias.
- `aplicarCupon()` valida vigencia, empresa, modalidad, disponibilidad y propiedad; después distribuye el descuento y actualiza pasajes y reserva.
- `removerCupon()` libera el código y restablece importes sin descuento.
- `recalcularCupon()` vuelve a aplicar el código cuando cambia la cantidad de pasajeros.
- `cancelarYLiberarCupon()` libera el cupón desde cancelaciones o vencimientos que ya controlan la transacción.
- `validarCuponAplicado()` comprueba el cupón antes de reportar el pago.

Los cupones aleatorios vuelven a estar disponibles al liberarse. Los personalizados eliminan el registro de uso creado para esa reserva. Los eventos de aplicación y liberación quedan en la auditoría JSON de la reserva.

