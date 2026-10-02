# Datos personales cifrados

El CRM cifra `users.telefono`, `viajeros.documento_identidad` y el snapshot `pasajes.viajero`. El CRM y el sitio público deben compartir la misma `APP_KEY` para leer estos atributos, o usar una estrategia común equivalente antes de conectarse a la misma base de datos.

`PII_HASH_KEY` debe tener el mismo valor seguro en ambos proyectos. Se utiliza para generar índices HMAC de búsqueda exacta y debe respaldarse junto con `APP_KEY`. No debe publicarse ni cambiarse sin un proceso de rotación, porque los hashes existentes dejarían de coincidir.

Los campos cifrados se almacenan como `TEXT` o `LONGTEXT`. Los campos terminados en `_hash` contienen HMAC SHA-256 normalizados y permiten buscar teléfonos y documentos por coincidencia exacta. No permiten búsquedas parciales con `LIKE`.

`viajeros` funciona como libreta editable del cliente. `pasajes.viajero` contiene un snapshot cifrado e inmutable con nombre, apellido, tipo y número de documento, fecha de nacimiento y tipo de pasajero. Modificar o eliminar un viajero frecuente no altera los pasajes emitidos anteriormente.

Las inserciones masivas de `OperacionHistoricaDemoSeeder` cifran los valores explícitamente porque `DB::table()->insert()` no ejecuta los casts de Eloquent.

`PersonalData::normalizarDocumento()` elimina caracteres no alfanuméricos y convierte a mayúsculas: `V-123.456` y `v123456` coinciden. No elimina el prefijo alfabético, por lo que `123456` es otro valor. `normalizarTelefono()` conserva únicamente dígitos; no unifica automáticamente prefijos internacionales con números locales. Un valor vacío genera hash nulo.

El HMAC no se descifra ni es una autorización: las consultas del cliente deben incluir `usuario_id`. El hash utiliza `config('app.pii_hash_key')`; el cifrado de los casts utiliza la clave de cifrado de Laravel.

## Escritura y búsqueda paso a paso

1. El consumidor asigna datos legibles al modelo Eloquent.
2. Los casts encrypted y encrypted:array cifran el atributo al almacenarlo.
3. Los eventos del modelo calculan el HMAC de búsqueda a partir del valor normalizado.
4. La base conserva el cifrado y el índice hash como campos separados.
5. Una búsqueda normaliza el texto recibido y calcula su HMAC con la misma llave.
6. La consulta compara hashes y limita el ámbito autorizado.
7. Al leer el atributo con Eloquent, Laravel descifra usando su configuración de cifrado.

El hash permite localizar la fila; no permite reconstruir el dato. Las inserciones directas deben reproducir cifrado y hash explícitamente porque no ejecutan eventos ni casts.
