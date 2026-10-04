# ViajeService de Empresas

`App\Services\Empresa\ViajeService::guardar` recibe el vendedor, ID nullable, lista ordenada de terminales, precios por clave origen-destino, minutos por segmento consecutivo, comentario y estatus. SaveViaje autoriza viajes/add o viajes/edit antes de invocarlo.

1. Valida terminales activos sin repetición, precios no negativos con hasta dos decimales y duraciones de 1 a 1440 minutos por segmento.
2. Abre una transacción. Al editar carga la ruta de esa empresa y bloquea su fila para serializar ediciones.
3. Conserva los extremos originales y exige mantener todas las combinaciones guardadas, sin eliminarlas ni invertirlas. Si existen programaciones rechaza cualquier cambio de recorrido.
4. Calcula duraciones acumuladas y guarda la ruta con empresa_id obtenido del usuario autenticado.
5. Genera todos los pares en orden de recorrido y exige precio explícito para cada uno. Nunca obtiene el precio combinado por suma.
6. Guarda posiciones de origen/destino, orden de fila, precio y duración calculada por combinación. Actualiza pares existentes y crea los nuevos; nunca elimina tramos.
7. Devuelve la ruta guardada. Cualquier error revierte toda la operación.

`viaje_tramos` es la plantilla completa; `Viaje::tramosConsecutivos()` filtra los pares cuyas posiciones difieren en uno y `secuenciaTerminales()` reconstruye el recorrido para disponibilidad y horarios. La programación conserva sus terminales, precio y horarios: cambiar una base no propaga cambios a salidas existentes.

El alta de programaciones aún no se incluye en este módulo. Al implementarla cargará todas las combinaciones, permitirá ajustar precios y solo guardará las seleccionadas en programacion_tramo_precios. No se añade un estado de venta a la plantilla por esa selección.

La migración original agrega las posiciones y una restricción única de ruta/origen/destino. Requiere recrear el esquema de desarrollo; no se ejecuta fresh automáticamente.
