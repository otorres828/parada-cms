# Documento de Arquitectura: Sistema de Pasajes por Tramos (O&D)

## 🌎 El Contexto del Usuario y la Experiencia de Interfaz

Cuando un usuario entra a tu sitio web y busca un pasaje de **São Paulo** a **Curitiba**, espera una experiencia fluida. Visualmente, el sistema opera con dos capas de información:

### 1. La Tarjeta del Viaje Principal (Vista Macroscópica)
El usuario no quiere ver tres opciones de buses diferentes si va a viajar en el mismo vehículo. El sistema agrupa todo en un único recuadro principal:
* **Encabezado:** Origen: São Paulo ➔ Destino: Curitiba
* **Datos Clave:** Hora de salida (08:00 AM), Hora de llegada (03:30 PM), Duración total (7h 30m) y el Precio Final del tramo largo (**R\$ 110,00**).

### 2. El Desglose del Itinerario (Vista Detallada)
Al hacer clic en "Ver detalles" o "Itinerario", la interfaz se expande para transparentar el recorrido físico del autobús. Esto le da confianza al pasajero y le explica por qué el viaje toma ese tiempo:
* 🚌 **08:00 AM** – Salida: São Paulo (Terminal Tietê)
* 🛑 **11:30 AM** – Parada 1: Registro (Terminal Rodoviário) – *Sube/baja gente o descanso*
* 🛑 **01:15 PM** – Parada 2: Registro (O otra ciudad intermedia)
* 🏁 **03:30 PM** – Llegada: Curitiba (Rodoviária)

---

## 🎛️ 1. La Configuración Técnico-Funcional: La Matriz de Tramos (O&D)

Para que la interfaz anterior sea posible, el backend no puede tratar el viaje como una línea indivisible. Se debe implementar técnicamente una **Matriz de Origen y Destino (O&D)**.

Si un autobús realiza el recorrido físico **A ➔ B ➔ C**, el sistema segmenta de forma virtual tres combinaciones comerciales independientes en la base de datos:
1. **Tramo 1:** Origen A ➔ Destino B
2. **Tramo 2:** Origen B ➔ Destino C
3. **Tramo 3:** Origen A ➔ Destino C (Viaje completo)

### 🗺️ El Manejo Inteligente de Asientos (Liberación por Tramos)
Si el usuario "Pedro" compra el **Asiento 12** para ir únicamente desde **São Paulo hasta Registro (Tramo 1)**, tu algoritmo de asignación debe bloquear el Asiento 12 *únicamente* durante ese espacio de tiempo y espacio geográfico.

Cuando el autobús llegue a Registro, Pedro se bajará. En ese instante, el sistema automáticamente debe marcar el **Asiento 12 como disponible** para cualquier otra persona que esté en la terminal de Registro buscando ir hacia **Curitiba (Tramo 2)**. De esta forma, un solo asiento físico puede generar múltiples ingresos en un mismo viaje.

---

## 📊 2. Lógica Comercial y Estrategia de Precios

El costo de un pasaje no se determina multiplicando linealmente los kilómetros. Se configura una matriz de precios basada en costos operativos fijos y estrategias de incentivos:

* **Estructura de costos fijos:** Cada terminal de autobuses (Rodoviária) cobra una tasa de embarque única. Al fraccionar el viaje, el sistema debe inyectar la tasa del terminal donde el pasajero aborda físicamente.
* **Incentivo de viaje largo:** Los tramos cortos suelen ser proporcionalmente más costosos para desincentivar que un pasajero de tramo corto te deje el autobús vacío a mitad de camino.

### 💡 Ejemplo Práctico de Configuración de Precios

| Tramo | Trayecto Comercial | Distancia | Costo Configurado | Comportamiento del Sistema |
| :--- | :--- | :--- | :--- | :--- |
| **Tramo 1** | São Paulo ➔ Registro | 180 km | **R\$ 60,00** | Tarifa base local + Tasa de São Paulo |
| **Tramo 2** | Registro ➔ Curitiba | 220 km | **R\$ 70,00** | Tarifa base local + Tasa de Registro |
| **Tramo Largo** | São Paulo ➔ Curitiba | 400 km | **R\$ 110,00** | **Precio con descuento aplicado** (Es menor que sumar 60 + 70) |

---

## 🚨 El Cuello de Botella en tus Migraciones Actuales

En las migraciones que diseñaste originalmente, el flujo de datos presenta dos problemas restrictivos:

1. **Precio estático y único:** Tu tabla `programaciones` tiene la columna `precio_pasaje`. Esto asume que no importa dónde suba o baje el usuario, el boleto siempre cuesta lo mismo.
2. **Ceguera de tramos en los asientos:** Tu tabla `pasajes` solo registra el `numero_asiento`, pero no sabe en qué tramo se está usando. Si alguien compra un pasaje de São Paulo a Registro, tu sistema actual decrementará `asientos_disponibles` en la tabla `programaciones` a nivel global, bloqueando el autobús completo para el resto de la ruta.

---

## 🛠️ Solución Planteada: Modificaciones en la Base de Datos

Para resolver esto, atomizaremos la lógica del recorrido. A continuación se detallan los cambios estructurales, el porqué de cada modificación y su impacto directo en los Modelos de Eloquent.

### 1. Nueva Tabla: `viaje_tramos`
* **Qué se hace:** Se crea una tabla que divide un `Viaje` en partes lógicas usando una columna `orden`.
* **Por qué se hace:** Para que el sistema sepa secuencialmente el orden de las paradas (Ej: São Paulo [Orden 1] ➔ Registro [Orden 2] ➔ Curitiba [Orden 3]).
* **Impacto en el Modelo (`Viaje.php`):** El modelo `Viaje` ahora tendrá una relación de **Uno a Muchos** (`hasMany`) con `ViajeTramo`.

### 2. Modificación en la Tabla: `programaciones`
* **Qué se hace:** Se **elimina** la columna `precio_pasaje`.
* **Por qué se hace:** El precio ya no es una propiedad global de la programación, ahora fluctúa según el origen y destino seleccionado.
* **Impacto en el Modelo (`Programacion.php`):** Deja de manejar atributos de precios directos.

### 3. Nueva Tabla: `programacion_tramo_precios`
* **Qué se hace:** Se crea la matriz O&D vinculando cada programación con todas sus combinaciones de terminales posibles, su precio específico y su tope de asientos (*bucket*).
* **Por qué se hace:** Permite definir de forma independiente que São Paulo-Registro cuesta R\$ 60 y São Paulo-Curitiba cuesta R\$ 110 en una fecha específica.
* **Impacto en el Modelo (`Programacion.php`):** Se añade una relación `hasMany` hacia `ProgramacionTramoPrecio`.

### 4. Modificación en la Tabla: `pasajes`
* **Qué se hace:** Se **agregan** dos columnas foráneas obligatorias: `origen_terminal_id` y `destino_terminal_id`.
* **Por qué se hace:** Al momento de verificar si el "Asiento 12" está libre, el sistema leerá los pasajes vendidos y sabrá exactamente desde qué ciudad y hasta qué ciudad estará ocupada esa butaca.
* **Impacto en el Modelo (`Pasaje.php`):** El pasaje ahora **pertenece a** (`belongsTo`) un Terminal de Origen y un Terminal de Destino de forma explícita.

---

## 🏁 Resultado Esperado en el Sistema

Una vez ejecutadas estas modificaciones, el comportamiento de tu plataforma web cambiará de forma drástica y profesional:

1. **Búsquedas Flexibles:** Si un cliente busca en tu web "Registro a Curitiba", tu sistema buscará las programaciones cuyos viajes pasen por esos terminales intermediarios y calculará el precio exacto mapeado en `programacion_tramo_precios`.
2. **Optimización de Inventario:** Dos personas distintas podrán comprar el **mismo asiento físico** (el número 12) en la misma programación, siempre y cuando sus tramos no se solapen (Pasajero 1: SP a Registro // Pasajero 2: Registro a Curitiba).
3. **Control Comercial Total:** Podrás limitar la venta de tramos cortos si deseas priorizar la venta del viaje largo, protegiendo tus márgenes de ganancia durante temporadas altas.
