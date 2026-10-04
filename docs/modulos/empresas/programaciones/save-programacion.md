# SaveProgramacion

Clase: `app/Livewire/Empresas/Programaciones/SaveProgramacion.php`.

## Entrada y permisos

Las rutas `empresas.programaciones.add` y `empresas.programaciones.edit` usan los permisos agregar y editar de Programaciones, comprobados por el middleware. Guardar vuelve a autorizar la acción. La clase extiende EmpresaComponent y todas las búsquedas se limitan a su empresa. El identificador de edición está protegido por Locked.

## Alta paso a paso

1. Seleccionar una ruta activa propia con tarifas base y un transporte activo propio que corresponda al tipo de empresa; se excluyen transportes plantilla.
2. Indicar la fecha y hora inicial. Al seleccionar la ruta se copian sus combinaciones y precios. Las duraciones consecutivas permiten sugerir fechas y horas, incluso al cruzar medianoche.
3. Ajustar los precios y horarios particulares. Recalcular horarios conserva precios y selección, pero reemplaza los horarios editados.
4. Desmarcar los trayectos que no se venderán. Se requiere al menos uno; los desmarcados no se guardan.
5. Guardar como programada (1) o inactiva (2). La capacidad se toma del transporte, nunca de un valor enviado por el navegador.

Las salidas de trayectos con un mismo origen deben coincidir; las llegadas a un mismo destino también. La llegada de cada trayecto debe ser posterior a su salida. Las fechas no pueden ser anteriores a hoy ni superar 2100. Los precios admiten cero y hasta dos decimales.

## Edición e integridad

Solo se editan programaciones programadas o inactivas sin reservas. Una programación finalizada o con cualquier reserva conserva transporte, horarios y tarifas. El servicio vuelve a comprobar estas condiciones bajo bloqueo de la programación, usado también por el proceso de reserva.

Las tarifas guardadas son una copia independiente: conservan origen, destino, precio y horarios en programacion_tramo_precios. Los cambios posteriores a viaje_tramos no se propagan. No se agregan fechas a programaciones.

## Resultado

Guarda todo en una transacción; un error revierte tanto la programación como sus tarifas. Vuelve al listado con alerta cuando el usuario puede listar; de lo contrario permanece en el formulario con confirmación. Alpine administra JustValidate y libera su validador y listeners al navegar.
