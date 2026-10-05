# SaveCampana

## Propósito y acceso

Alta y edición de campañas de cupones de la empresa autenticada. Las rutas `empresas.cupones.add` y `empresas.cupones.edit` están protegidas por los permisos `cupones/add` y `cupones/edit`. El botón Nuevo registro del listado depende de `canAdd`; editar depende de `canEdit`.

## Carga y guardado

La clase extiende `EmpresaComponent`. `mount` consulta la campaña propia y pasa el modelo a `editar` para asignar los campos; una campaña ajena devuelve 404. `render` presenta una vista independiente de Admin, sin selector de empresa. `save` autoriza de nuevo la escritura, valida con mensajes en español y asigna `empresa_id` desde el usuario autenticado.

La transacción guarda la configuración y, para altas aleatorias, llama a `CuponService::crearCuponesAleatorios`. Los personalizados se normalizan a mayúsculas y son únicos; sus redenciones se crean al utilizarlos. Al terminar se regresa al listado con la alerta de éxito.

## Reglas

Se permiten descuentos por porcentaje o monto fijo, aplicados a la reserva o a cada pasaje. El porcentaje no supera 100; la cantidad va de 1 a 1000 y el fin debe ser posterior al inicio. Los estados son 1 activo y 2 inactivo.

Cuando existen cupones, se conservan empresa, código, cantidad, tipo de cupón, tipo y monto del descuento, aplicación e inicio. Se pueden actualizar nombre, modalidad, fin y estado conforme al comportamiento del alta administrativa. Editar no vuelve a generar cupones.

## Vista

Utiliza los componentes de formulario existentes y Alpine local con JustValidate. Los errores del servidor aparecen en `x-layout.error` y junto a los campos. Volver al listado se muestra sin condición `canList`, conforme al estándar Save.
