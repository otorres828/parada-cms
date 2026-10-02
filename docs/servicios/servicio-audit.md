# Admin\Audit

Fuente: `app/Services/Admin/Audit.php`.

`record(string $action, Model $record, array $data = [])` crea una auditoría con el administrador del guard `admin`, acción, nombre de la entidad, ID del registro, datos adicionales e IP de la solicitud.

Omite las claves de primer nivel `password`, `remember_token`, `datos_bancarios`, `comprobante`, `token` y `secret`. No inspecciona estructuras anidadas ni detecta datos personales por su contenido: quien llama al servicio debe seleccionar los datos que registra.

La auditoría se genera cuando el código llama explícitamente a `record()`; no intercepta automáticamente todos los cambios de la base de datos.

## Registro paso a paso

1. Recibe acción, modelo persistido y datos adicionales.
2. Excluye únicamente las claves sensibles explícitas de primer nivel.
3. Obtiene el ID del actor del guard admin y la IP de la solicitud.
4. Crea Auditoria con la clase corta del modelo y su clave primaria.

Si el llamador está dentro de una transacción, el registro participa en ella. La llamada no abre por sí misma una transacción para el cambio de negocio.
