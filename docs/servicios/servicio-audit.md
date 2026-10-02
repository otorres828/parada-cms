# Admin\Audit

Fuente: `app/Services/Admin/Audit.php`.

`record(string $action, Model $record, array $data = [])` crea una auditoría con el administrador del guard `admin`, acción, nombre de la entidad, ID del registro, datos adicionales e IP de la solicitud.

Omite las claves de primer nivel `password`, `remember_token`, `datos_bancarios`, `comprobante`, `token` y `secret`. No inspecciona estructuras anidadas ni detecta datos personales por su contenido: quien llama al servicio debe seleccionar los datos que registra.

La auditoría se genera cuando el código llama explícitamente a `record()`; no intercepta automáticamente todos los cambios de la base de datos.
