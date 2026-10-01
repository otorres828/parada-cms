# Datos personales cifrados

El CRM cifra `users.telefono`, `viajeros.documento_identidad` y el snapshot `pasajes.viajero`. El CRM y el sitio público deben compartir la misma `APP_KEY` para leer estos atributos, o usar una estrategia común equivalente antes de conectarse a la misma base de datos.

`PII_HASH_KEY` debe tener el mismo valor seguro en ambos proyectos. Se utiliza para generar índices HMAC de búsqueda exacta y debe respaldarse junto con `APP_KEY`. No debe publicarse ni cambiarse sin un proceso de rotación, porque los hashes existentes dejarían de coincidir.

Los campos cifrados se almacenan como `TEXT` o `LONGTEXT`. Los campos terminados en `_hash` contienen HMAC SHA-256 normalizados y permiten buscar teléfonos y documentos por coincidencia exacta. No permiten búsquedas parciales con `LIKE`.

`viajeros` funciona como libreta editable del cliente. `pasajes.viajero` contiene un snapshot cifrado e inmutable con nombre, apellido, tipo y número de documento, fecha de nacimiento y tipo de pasajero. Modificar o eliminar un viajero frecuente no altera los pasajes emitidos anteriormente.

Las inserciones masivas de `OperacionHistoricaDemoSeeder` cifran los valores explícitamente porque `DB::table()->insert()` no ejecuta los casts de Eloquent.
