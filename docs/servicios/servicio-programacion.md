# ProgramacionService

Servicio empresarial: `App\Services\Empresa\ProgramacionService`.

`guardar(empresaId, datos, programacionId)` recibe ruta, transporte, estatus y combinaciones habilitadas con precios y horarios. La pantalla autoriza la escritura; el servicio valida los datos y la pertenencia a la empresa.

Dentro de una transacción consulta ruta y transporte activos propios, comprueba el tipo de transporte y excluye plantillas. En edición bloquea la programación para coordinarse con la creación de reservas. Rechaza programaciones finalizadas o con reservas.

Cada clave origen-destino debe existir en la plantilla. Los trayectos desmarcados no se persisten; los restantes deben tener precio válido, horarios completos, llegada posterior a salida y horarios coherentes para terminales compartidos. Exige al menos uno. Guarda capacidad desde el transporte y sustituye las tarifas únicamente cuando no hay reservas. Cualquier fallo revierte la operación completa.

Los terminales y precios son copias independientes en programacion_tramo_precios, no relaciones mutables con la plantilla. No modifica Viaje ni ViajeTramo.
