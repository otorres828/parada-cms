# ProgramacionService

Servicio empresarial: `App\Services\Empresa\ProgramacionService`.

`guardar(empresaId, datos, programacionId)` recibe ruta, transporte, estatus y combinaciones habilitadas con precios y horarios. La pantalla autoriza la escritura; el servicio valida los datos y la pertenencia a la empresa.

Dentro de una transacción consulta ruta y transporte activos propios, comprueba el tipo de transporte y excluye plantillas. En edición bloquea la programación para coordinarse con la creación de reservas. Rechaza programaciones finalizadas o con reservas.

Cada clave origen-destino debe existir en la plantilla. Los trayectos desmarcados no se persisten; los restantes deben tener precio válido, horarios completos, llegada posterior a salida y horarios coherentes para terminales compartidos. Exige al menos uno. Guarda capacidad desde el transporte y sustituye las tarifas únicamente cuando no hay reservas. Cualquier fallo revierte la operación completa.

Los terminales y precios son copias independientes en programacion_tramo_precios, no relaciones mutables con la plantilla. No modifica Viaje ni ViajeTramo.

## Creación por fechas

El alta permite una fecha, un rango inclusivo con días de la semana (lunes=1 a domingo=7), o una lista de fechas específicas. La edición continúa siendo individual. No necesita tablas nuevas.

`ProgramacionService::fechas` valida y ordena las fechas, rechaza duplicados y fechas pasadas, exige al menos una salida y limita el lote a 366 programaciones; el rango también puede abarcar como máximo 366 días. El formulario presenta el número de salidas y todas sus fechas antes de guardar.

`guardarLote` toma la fecha inicial del formulario como referencia de los horarios configurados. Para cada fecha seleccionada desplaza las fechas de salida y llegada de todos los trayectos habilitados por la misma cantidad de días; conserva las horas, precios y días adicionales del recorrido. La fecha inicial sirve como referencia incluso en modalidad de fechas específicas. Si se modifica la fecha inicial después de configurar los trayectos, se pueden actualizar con Recalcular horarios sugeridos.

Crea una programación independiente por fecha dentro de una transacción exterior. La validación y pertenencia empresarial de cada copia se comprueban mediante guardar. Un fallo en cualquiera revierte el lote completo, incluidas las tarifas ya insertadas. No actualiza programaciones anteriores ni interpreta el lote como una regla recurrente: las fechas elegidas se crean ahora. Tras un alta sin permiso de listado se limpian ruta, transporte, trayectos y fechas específicas para evitar volver a guardar accidentalmente el mismo formulario.
