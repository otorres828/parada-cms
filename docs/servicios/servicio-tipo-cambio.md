# TipoCambioService

`actualizar()` consulta el endpoint BCV configurado en `services.bcv`, exige valores numéricos positivos para USD y EUR y crea un registro de `TipoCambio` con dos decimales.

La solicitud usa respuesta JSON, tiempo máximo configurable y hasta tres intentos. Una respuesta incompleta o inválida produce una excepción y no registra tasas parciales.

