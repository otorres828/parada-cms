# ReservaTaquillaService

`registrar` es la entrada de la venta completa. Recibe vendedor, tarifa, comprador, pasajeros, pagos y un código opcional para reintentos.

1. Autoriza empresa activa y permiso de venta.
2. Abre una transacción y bloquea la programación para coordinar la disponibilidad con otras ventas.
3. Si el código ya está registrado por ese vendedor en esa empresa, devuelve esa venta.
4. Crea la reserva, guarda el comprador cifrado y añade los snapshots de pasajeros. Estas operaciones no se confirman por separado desde la pantalla.
5. Cada pasajero con asiento comprueba cupo por tramo y recibe un asiento libre; un infante sin asiento guarda numero_asiento nulo y todos sus importes a cero.
6. Delega los cobros a PagoTaquillaService. Cualquier error revierte reserva, pasajes y pagos.

Los métodos crear, guardarComprador, agregarPasajero y removerPasajero conservan su validación para operaciones internas. La clase Livewire no expone acciones para persistir borradores. validarPasajero valida datos sin escribir, para el formulario de cotización.

La tasa es cero por origen taquilla, con independencia del contrato. No se crean cuentas User ni viajeros asociados al vendedor. El correo se envía solo mediante acción explícita posterior a una venta pagada.
