# Contexto de Arquitectura: Sistema de Pasajes por Tramos (O&D)

Estoy desarrollando un sitio web de compra de pasajes de autobús en Laravel. Necesito diseñar la lógica del backend basándome en una arquitectura de **Matriz de Origen y Destino (O&D)** para gestionar rutas con paradas/escalas intermedias utilizando un solo autobús físico.

A continuación, se detalla el comportamiento esperado de la plataforma, las reglas de negocio y los cambios estructurales requeridos en la base de datos.

---

## 🌎 1. Experiencia de Interfaz e Itinerario

Cuando un usuario busca un pasaje de **São Paulo** a **Curitiba**, el sistema debe operar con dos capas de información visual:

1. **La Tarjeta del Viaje Principal:** Muestra el viaje completo como un único elemento agrupado. Indica: **Origen: São Paulo ➔ Destino: Curitiba**, hora de salida (08:00 AM), hora de llegada (03:30 PM), duración y precio final del tramo largo (**R\$ 110,00**).
2. **El Desglose del Itinerario:** Al hacer clic en "Ver detalles", se despliega la lista secuencial de paradas físicas:
   * 🚌 **08:00 AM** – Salida: São Paulo (Terminal Tietê)
   * 🛑 **11:30 AM** – Parada 1: Registro (Terminal Rodoviário) – *Sube/baja gente o descanso*
   * 🛑 **01:15 PM** – Parada 2: Registro (U otra ciudad intermedia)
   * 🏁 **03:30 PM** – Llegada: Curitiba (Rodoviária)

---

## 🎛️ 2. Configuración Técnica: Matriz de Tramos y Control de Asientos

El autobús físico realiza el recorrido **A ➔ B ➔ C**. El sistema debe segmentar de forma virtual tres combinaciones comerciales independientes en la base de datos:
1. **Tramo 1:** Origen A ➔ Destino B (São Paulo a Registro)
2. **Tramo 2:** Origen B ➔ Destino C (Registro a Curitiba)
3. **Tramo 3:** Origen A ➔ Destino C (São Paulo a Curitiba - Viaje completo)

### 🗺️ Manejo Inteligente de Asientos (Liberación por Tramos)
* Si el Pasajero 1 compra el **Asiento 12** para el **Tramo 1 (A ➔ B)**, ese asiento se bloquea únicamente en ese tramo.
* Cuando el autobús llega a **B**, el Pasajero 1 se baja. El sistema debe marcar automáticamente el **Asiento 12 como disponible** para que el Pasajero 2 pueda comprarlo para el **Tramo 2 (B ➔ C)**.
* Un mismo asiento físico puede venderse múltiples veces en el mismo viaje, siempre y cuando los tramos no se solapen matemáticamente.

---

## 📊 3. Lógica Comercial y Estrategia de Precios

Los precios se configuran de forma independiente por tramo y no son estrictamente proporcionales a los kilómetros:
* **Tasas de Embarque:** Cada terminal cobra una tasa fija que se suma automáticamente según el terminal donde el pasajero aborde físicamente.
* **Incentivo de Larga Distancia:** Los tramos cortos se configuran proporcionalmente más caros para evitar que dejen el autobús vacío a mitad de camino.

### 💡 Ejemplo Práctico de Precios
* **Tramo 1 (São Paulo ➔ Registro):** R\$ 60,00
* **Tramo 2 (Registro ➔ Curitiba):** R\$ 70,00
* **Tramo Completo (São Paulo ➔ Curitiba):** R\$ 110,00 *(Descuento comercial: es menor que la suma de los tramos individuales)*

---

## 🛠️ Solución Estructural: Modificaciones en la Base de Datos

Para migrar el sistema desde un enfoque lineal hacia este enfoque por tramos, se definen las siguientes modificaciones en las migraciones de Laravel:

### 1. Nueva Tabla: `viaje_tramos`
Define el recorrido físico y la secuencia lógica de las paradas mediante una columna `orden`.
* **Impacto en Eloquent:** El modelo `Viaje` tiene una relación `hasMany` con `ViajeTramo`.

### 2. Modificación en la Tabla: `programaciones`
Se **elimina** la columna `precio_pasaje`, ya que el precio deja de ser una propiedad global del viaje y pasa a depender de los terminales seleccionados.

### 3. Nueva Tabla: `programacion_tramo_precios`
Es la matriz O&D. Vincula cada programación con todas sus combinaciones posibles de terminales, asignando su precio específico y un tope opcional de asientos (`asientos_maximos_permitidos` para buckets de tramos cortos).
* **Impacto en Eloquent:** El modelo `Programacion` tiene una relación `hasMany` con `ProgramacionTramoPrecio`.

### 4. Modificación en la Tabla: `pasajes`
Se **agregan** las columnas foráneas `origen_terminal_id` y `destino_terminal_id`. Al registrar un pasaje vendido, el sistema conoce con precisión las coordenadas exactas de ocupación de ese asiento.
* **Impacto en Eloquent:** El modelo `Pasaje` tiene relaciones `belongsTo` hacia los modelos de terminales de origen y destino.
