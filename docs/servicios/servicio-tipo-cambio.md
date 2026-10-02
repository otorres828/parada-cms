# TipoCambioService

`actualizar()` consulta el endpoint BCV configurado en `services.bcv`, exige valores numéricos positivos para USD y EUR y crea un registro de `TipoCambio` con dos decimales.

La solicitud usa respuesta JSON, tiempo máximo configurable y hasta tres intentos. Una respuesta incompleta o inválida produce una excepción y no registra tasas parciales.

## Actualización paso a paso

1. Crea un cliente HTTP que acepta JSON y usa el timeout configurado.
2. Solicita el endpoint con reintentos; una respuesta HTTP fallida genera excepción.
3. Lee USD y EUR y comprueba que ambos sean numéricos y positivos.
4. Trunca a dos decimales mediante bcdiv; no redondea al centavo superior.
5. Inserta un registro nuevo con la fecha actual y lo devuelve.

La historia se conserva porque la actualización crea una fila en lugar de sobrescribir la anterior.
