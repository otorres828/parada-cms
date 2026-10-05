# SaveReprogramacion

Alta únicamente: selecciona una reserva pagada mediante búsqueda por código, ID o comprador y elige otra salida del mismo origen y destino. No permite modificar ni retirar pasajeros. Extiende EmpresaComponent; mount prepara cuentas y referencia, render cotiza, save confirma en el servicio. Todas las consultas y la escritura están limitadas a la empresa autenticada.

Antes de guardar muestra confirmación con tramo, nueva salida, pasajeros e importes. Solo cobra la diferencia cuando el subtotal nuevo supera al original. Si coincide no solicita pagos; si es menor se rechaza. Reutiliza transporte y pagos de taquilla con conversión USD/BS. Los datos del formulario no crean borradores.

Requiere reprogramaciones/add. Al terminar guarda el flash empresas_reprogramacion_success y redirige al listado. No existen rutas de edición ni eliminación.

La búsqueda se ejecuta en `buscarReservas`, llamada desde mount y updatedSearchReserva. Asigna una colección Eloquent protegida a `$this->reservas` y muestra los motivos de rechazo si no hay resultados elegibles. Render no realiza la búsqueda de reservas.
