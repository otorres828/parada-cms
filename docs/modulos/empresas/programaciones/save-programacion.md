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

## Creación por fechas

El alta permite una fecha, un rango inclusivo con días de la semana (lunes=1 a domingo=7), o una lista de fechas específicas. La edición continúa siendo individual. No necesita tablas nuevas.

`ProgramacionService::fechas` valida y ordena las fechas, rechaza duplicados y fechas pasadas, exige al menos una salida y limita el lote a 366 programaciones; el rango también puede abarcar como máximo 366 días. El formulario presenta el número de salidas y todas sus fechas antes de guardar.

`guardarLote` toma la fecha inicial del formulario como referencia de los horarios configurados. Para cada fecha seleccionada desplaza las fechas de salida y llegada de todos los trayectos habilitados por la misma cantidad de días; conserva las horas, precios y días adicionales del recorrido. La fecha inicial sirve como referencia incluso en modalidad de fechas específicas. Si se modifica la fecha inicial después de configurar los trayectos, se pueden actualizar con Recalcular horarios sugeridos.

Crea una programación independiente por fecha dentro de una transacción exterior. La validación y pertenencia empresarial de cada copia se comprueban mediante guardar. Un fallo en cualquiera revierte el lote completo, incluidas las tarifas ya insertadas. No actualiza programaciones anteriores ni interpreta el lote como una regla recurrente: las fechas elegidas se crean ahora. Tras un alta sin permiso de listado se limpian ruta, transporte, trayectos y fechas específicas para evitar volver a guardar accidentalmente el mismo formulario.

## Presentación del formulario

Ruta y transporte se eligen en el primer bloque. La fecha de referencia, hora, modalidad, selección de fechas y actualización de horarios se agrupan en Fechas de programación. En edición este mismo bloque conserva fecha y hora, sin modalidades de lote. Después aparecen los trayectos, el selector de estatus de ancho limitado y el botón Guardar alineado a la izquierda, separado mediante una línea, como en el formulario de Empresas.

## Catálogos del formulario

Las rutas y transportes se cargan en mount en propiedades públicas tipadas como colecciones de Eloquent y protegidas con Locked. Render consume esas propiedades y calcula la vista previa de fechas; no vuelve a ejecutar las búsquedas de catálogos. El servicio mantiene sus comprobaciones de pertenencia y estado actual al guardar.

## Puestos a vender por trayecto

La columna Puestos a vender permite fijar un límite independiente para cada combinación. Vacío significa todos los puestos de la programación; un valor debe estar entre 1 y la capacidad actual del transporte. Se guarda en asientos_maximos_permitidos y se conserva en cada copia del lote. La disponibilidad existente utiliza ese límite y la ocupación de los segmentos compartidos. Cambiar después la capacidad del transporte no modifica asientos_totales ni los límites guardados en programaciones anteriores.
# Fechas calculadas por recorrido

El listado de trayectos se presenta en tarjetas por terminal de origen, conservando el orden de la ruta y las filas de cada combinación. Cada tarjeta identifica el terminal de salida y mantiene las mismas columnas de venta, horarios, precio y puestos. En pantallas estrechas cada tabla permite desplazamiento horizontal.

La tabla permite introducir horas de salida y llegada; debajo de cada hora muestra la fecha calculada. `ProgramacionService::normalizarHorarios()` recorre los terminales en el orden de la ruta, primero llegada y después salida. Si una hora es anterior al evento previo, avanza un día. Una salida puede coincidir con la llegada a esa parada; la llegada al siguiente terminal debe ser posterior a la salida.

Las combinaciones no se interpretan como filas consecutivas. A→B, A→C y B→C comparten los horarios de A, B y C. Modificar una hora actualiza las combinaciones del mismo terminal y recalcula las fechas desde la fecha de referencia. Por ejemplo, A→B 06:00–09:00 y B→C 09:30–05:00 implican llegada a C al día siguiente también para A→C.

La normalización se ejecuta de nuevo en el servicio antes de guardar. Cada programación del lote conserva los cambios de día relativos a su propia fecha de referencia.
