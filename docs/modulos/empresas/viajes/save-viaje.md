# SaveViaje — alta y edición de rutas

Clase: `app/Livewire/Empresas/Viajes/SaveViaje.php`. Vista: `resources/views/livewire/empresas/viajes/save-viaje.blade.php`.

## Acceso y permisos

Rutas `empresas.viajes.add` y `empresas.viajes.edit`, protegidas por CheckPermissionEmpresa con viajes/add y viajes/edit. El guard y empresa_id proceden de EmpresaComponent. Mount carga únicamente rutas de la empresa autenticada y calcula canList; save autoriza nuevamente la acción. El listado muestra Nuevo y Editar según los permisos calculados en mount.

## Uso

1. Seleccionar el origen.
2. Seleccionar un terminal y pulsar Añadir. Cada incorporación se convierte en el último destino; las anteriores quedan como paradas intermedias.
3. Revisar el recorrido visual A, B, C… e indicar los minutos entre paradas consecutivas.
4. Completar el precio independiente de todas las combinaciones generadas, en orden A→B, A→C, A→D, B→C, B→D, C→D.
5. Elegir estatus, añadir comentario opcional y guardar.

Los cambios previos a Guardar son solo propiedades de Livewire. Al añadir o retirar paradas se conservan los precios de pares que siguen existiendo. Los nuevos precios y duraciones quedan pendientes de completar. Se admiten entre 2 y 20 terminales distintos y activos.

## Edición

Origen y destino no cambian. Una nueva parada se inserta antes del destino final. No se pueden retirar paradas ni eliminar o invertir tramos existentes, incluso si la ruta todavía no tiene programaciones. El servidor verifica que todas las combinaciones guardadas sigan presentes. Solo durante el alta se permite retirar destinos antes de guardar. El servidor verifica los extremos contra el registro original, aunque se manipulen las propiedades del navegador.

Si la ruta ya tiene programaciones no se permite alterar su secuencia: sus cálculos de disponibilidad todavía dependen del recorrido. Se pueden cambiar los precios base, comentarios, duraciones estimadas y estatus sin reescribir las tarifas u horarios existentes. Para otro recorrido, crear otra ruta.

## Componentes y validación

`recorrido-form` dibuja las paradas y sus duraciones; `tarifas-form` muestra todas las combinaciones. El Alpine `saveViaje` reconstruye JustValidate antes de enviar para incluir campos dinámicos y elimina su instancia al salir. Los mensajes del servidor son en español. No se consulta permisos desde Blade.

La persistencia se delega a [ViajeService](../../../servicios/servicio-viaje.md), en una transacción. Redirige al listado con mensaje de éxito cuando existe permiso para listar. Sin ese permiso, muestra la alerta en la pantalla.

Prueba: `tests/ViajesEmpresaSmoke.php`, SQLite en memoria. Cubre alta, combinaciones, precios independientes, edición, extremos inmutables, protección de rutas programadas, aislamiento, permisos y renderizado de vistas.
