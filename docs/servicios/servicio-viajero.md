# ViajeroService: viajeros y datos históricos de los pasajes

Fuente: `app/Services/ViajeroService.php`, con las entidades `Viajero` y `Pasaje`.

## Objetivo

Un viajero es una persona guardada por un cliente para utilizarla durante la compra de pasajes. No representa una compra ni ocupa un asiento por sí mismo. La ocupación se produce cuando el viajero se incorpora a una reserva y se crea el pasaje correspondiente.

El sistema separa dos conceptos:

- `viajeros`: libreta de personas frecuentes perteneciente al cliente.
- `pasajes.viajero`: copia histórica de los datos utilizados al emitir el pasaje.

Esta separación permite que el cliente corrija o elimine posteriormente un viajero sin modificar boletos comprados con anterioridad.

## Propiedad del viajero

Cada viajero pertenece a un único cliente mediante `viajeros.usuario_id`. Las operaciones reciben al cliente autorizado por el consumidor; el servicio no consulta la sesión. La eliminación y la incorporación a una reserva comprueban su propiedad.

Conocer un ID no permite consultar, agregar o eliminar el viajero de otro cliente. Las consultas incluyen simultáneamente el ID del viajero y el ID del cliente.

## Estados

Los estados disponibles son:

| Valor | Estado | Uso |
| --- | --- | --- |
| `1` | Activo | Puede incorporarse a una reserva nueva. |
| `2` | Inactivo | Se conserva, pero no puede utilizarse en compras nuevas. |
| `0` | Eliminado | Ya no debe mostrarse ni utilizarse para nuevas compras. |

`ReservaService::agregarPasajero()` acepta únicamente viajeros activos.

Las constantes se heredan de `ModelHelper`: `ESTADO_ACTIVE`, `ESTADO_INACTIVE` y `ESTADO_DELETE`.

## Creación

La creación se realiza con:

```php
ViajeroService::agregarViajero($cliente, $datos);
```

El servicio valida:

- Nombre y apellido.
- Tipo de documento permitido.
- Documento opcional. Si se proporciona, su tipo es obligatorio; el servicio no exige documento por edad.
- Fecha de nacimiento válida y no posterior al día actual.
- Tipo de pasajero: adulto, niño o infante.
- Asociación con el cliente que realiza la operación.

El viajero se crea inicialmente con estado activo.

## Incorporación a una reserva

La reserva no crea viajeros. El viajero debe existir antes de incorporarlo:

```php
ReservaService::agregarPasajero(
    cliente: $cliente,
    reservaId: $reservaId,
    viajeroId: $viajeroId,
);
```

Antes de crear el pasaje se comprueba que:

- La reserva pertenece al cliente.
- La reserva está nueva, vigente, sin pago y puede modificarse.
- El viajero pertenece al mismo cliente.
- El viajero se encuentra activo.
- El viajero todavía no está incluido en esa reserva.
- Existe cupo para el tramo seleccionado.

Cuando las validaciones se cumplen, se asigna un asiento disponible, se crea el pasaje y se recalculan cupones, tasas de servicio y totales.

## Referencia y snapshot histórico

El pasaje guarda simultáneamente:

```text
pasajes.viajero_id  -> referencia nullable al viajero original
pasajes.viajero     -> snapshot cifrado de los datos utilizados
```

`viajero_id` permite determinar si un viajero guardado fue utilizado. La clave foránea utiliza `ON DELETE SET NULL`: si el viajero puede eliminarse físicamente, el pasaje se conserva y solamente pierde la referencia.

Los pasajes creados antes de incorporar `viajero_id` se reconocen mediante la combinación del hash del documento y el cliente propietario de la reserva. Esta compatibilidad evita borrar físicamente un viajero que ya tenía historial. Los viajeros históricos sin documento no pueden vincularse retrospectivamente con certeza; los pasajes nuevos siempre guardan `viajero_id`, incluso cuando el documento es opcional.

El snapshot contiene:

```json
{
    "version": 1,
    "nombre": "...",
    "apellido": "...",
    "tipo_documento": 1,
    "documento_identidad": "...",
    "fecha_nacimiento": "YYYY-MM-DD",
    "tipo_pasajero": "adulto"
}
```

El ID no se incluye dentro del JSON porque ya se guarda en `viajero_id`. El snapshot representa datos históricos y no una relación dinámica.

Si el cliente cambia posteriormente el nombre o documento en `viajeros`, los pasajes anteriores continúan mostrando los datos existentes al momento de la compra.

## Cifrado

`viajeros.documento_identidad` utiliza el cast de Laravel `encrypted`. Laravel cifra el valor antes de guardarlo y lo descifra automáticamente al leer el atributo desde el modelo.

El snapshot completo de `pasajes.viajero` utiliza `encrypted:array`. El arreglo se serializa y cifra antes de almacenarse. Al acceder a `$pasaje->viajero`, Laravel devuelve nuevamente el arreglo descifrado.

La base de datos no conserva estos valores en texto legible. La seguridad del cifrado depende de `APP_KEY`; esa llave debe respaldarse de forma segura. Perderla impediría descifrar viajeros y pasajes existentes.

## Hashes de documentos

El cifrado de Laravel no permite realizar búsquedas directas porque un mismo valor cifrado puede producir representaciones distintas. Por ese motivo existen:

- `viajeros.documento_identidad_hash`
- `pasajes.viajero_documento_hash`

`PersonalData::hashDocumento()` normaliza el documento y genera un HMAC. Para el mismo documento normalizado y la misma llave, siempre devuelve el mismo hash.

El hash permite búsquedas exactas mediante Eloquent sin descifrar todas las filas:

```php
$hash = PersonalData::hashDocumento($documento);

Viajero::where('usuario_id', $cliente->id)
    ->where('documento_identidad_hash', $hash)
    ->first();
```

No permite búsquedas parciales con `LIKE`. El HMAC se utiliza en lugar de un SHA simple porque los números de documento poseen un espacio de valores predecible y serían más fáciles de adivinar mediante tablas precalculadas.

El hash no sustituye el dato cifrado y no se muestra en vistas, exportaciones ni respuestas públicas.

## Retirar un pasajero de una reserva

```php
ReservaService::removerPasajero($cliente, $reservaId, $pasajeId);
```

Esta operación elimina el pasaje de una reserva nueva y recalcula sus importes. No elimina el viajero de la libreta del cliente.

Si se retira el último pasajero, la reserva vuelve a su cotización inicial de un pasaje y libera el cupón aplicado. Conserva su fecha de expiración, pero ya no ocupa asientos.

## Eliminación de viajeros

La eliminación se realiza con:

```php
ViajeroService::eliminarViajero($cliente, $viajeroId);
```

El resultado depende de su utilización:

| Situación | Comportamiento |
| --- | --- |
| No tiene pasajes relacionados actualmente | El registro se elimina físicamente, incluso si tuvo pasajes que ya fueron retirados. |
| Tiene pasajes históricos | Se conserva y cambia a estado eliminado (`0`). |
| Está incluido en una reserva nueva, vigente y sin pago | La operación se rechaza. |
| Solo aparece en una reserva nueva expirada | Puede pasar a estado eliminado; la reserva expirada se gestiona por su proceso correspondiente. |

Una reserva abierta produce el mensaje:

> Este viajero está incluido en una reserva abierta. Retíralo de la reserva antes de eliminarlo.

No se elimina automáticamente el pasaje porque hacerlo podría liberar un asiento, modificar un cupón y cambiar tasas o totales sin que el cliente lo espere. Primero debe retirarse explícitamente del checkout.

## Integridad y concurrencia

La incorporación y eliminación se ejecutan dentro de transacciones.

- Al incorporar se bloquean la reserva, el viajero y la programación.
- Al eliminar se bloquea únicamente el viajero solicitado.
- No se bloquean tablas completas ni listados generales.

Esto evita que un viajero sea eliminado mientras otra petición intenta incorporarlo a una reserva y mantiene consistentes el asiento, el snapshot y los totales.

## Privacidad operacional

Aunque los datos estén cifrados, deben mantenerse las autorizaciones por cliente y los permisos administrativos. También debe evitarse registrar documentos completos en logs, errores o auditorías.

Las exportaciones que incluyan documentos deben tratarse como archivos privados, con acceso autorizado y una política de eliminación. El cifrado de la base de datos no protege un documento después de exportarlo a Excel.

## Eliminación paso a paso

1. Abre una transacción y bloquea al viajero filtrando por su ID y cliente.
2. Busca pasajes con ese viajero_id; para pasajes sin referencia contempla el mismo hash de documento y propietario.
3. Si encuentra una reserva nueva, vigente y sin pago, lanza la validación y no elimina nada.
4. Si quedan pasajes relacionados marca ESTADO_DELETE.
5. Si no quedan pasajes relacionados elimina físicamente la fila.

Retirar un pasaje del checkout y borrar un viajero de la libreta son acciones seRodandos. Un documento modificado después de un pasaje sin viajero_id puede impedir la asociación histórica por hash; los pasajes con referencia directa no tienen esa limitación.
