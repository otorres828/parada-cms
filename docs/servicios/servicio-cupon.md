# CuponService

## Objetivo

Administra la generación, aplicación, recálculo y liberación de cupones. La campaña define si el descuento es fijo o porcentual, si aplica a toda la reserva o individualmente a cada pasaje y qué modalidad de clientes puede utilizarlo.

## Operaciones

- `crearCuponesAleatorios()` genera una sola tanda de códigos únicos para campañas aleatorias.
- `aplicarCupon()` valida vigencia, empresa, modalidad y disponibilidad; después distribuye el descuento y actualiza pasajes y reserva. El llamador debe entregar una reserva autorizada: este método no recibe un cliente para comprobar su propiedad.
- `removerCupon()` libera el código y restablece importes sin descuento.
- `recalcularCupon()` vuelve a aplicar el código cuando cambia la cantidad de pasajeros.
- `cancelarYLiberarCupon()` libera el cupón desde cancelaciones o vencimientos que ya controlan la transacción.
- `validarCuponAplicado()` comprueba el cupón antes de reportar el pago.

Los cupones aleatorios vuelven a estar disponibles al liberarse. Los personalizados eliminan el registro de uso creado para esa reserva. Los eventos de aplicación y liberación quedan en la auditoría JSON de la reserva.

Aplicar o retirar un cupón deja las tasas en cero y borra sus snapshots para recalcularlas. El consumidor debe preparar el resumen mediante `ReservaService` antes de mostrar el total definitivo. El cupón se marca redimido al aplicarse, antes de confirmar el pago.

## Aplicación paso a paso

1. Abre una transacción y bloquea la reserva, salvo que el llamador ya la entregue bloqueada.
2. Exige una reserva editable con pasajes y normaliza el código a mayúsculas.
3. Si ya tiene ese código aplicado devuelve la reserva; si cambia de código libera primero el anterior.
4. Bloquea cupón y campaña y comprueba vigencia, empresa y límite de usos.
5. Para primera compra busca reservas anteriores pagadas, reprogramadas o reembolsadas; para usuario nuevo compara la creación del cliente con el inicio de campaña.
6. Registra el uso personalizado o asigna el código aleatorio al cliente.
7. Calcula y distribuye descuentos, limita cada descuento al precio y asigna el residuo de redondeo al último pasaje.
8. Limpia tasas y snapshots para su cálculo posterior, guarda auditoría y devuelve relaciones actualizadas.

Ejemplo: tres pasajes de 30 con un cupón fijo de 1 generan descuento total de 1 si aplica en reserva, o de 3 si aplica en pasajes. Antes de cobrar se ejecuta prepararResumen para calcular la tasa sobre los subtotales.

El parámetro reservaBloqueada es de uso interno; no debe aceptarse desde el navegador.
