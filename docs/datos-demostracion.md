# Datos de demostración

Los datos operativos de prueba están separados en `database/seeders/Test`:

- `AdminDemoSeeder`: coordina la ejecución de los seeders de prueba.
- `CatalogosDemoSeeder`: crea tipo de cambio, amenidades y terminales.
- `EmpresasDemoSeeder`: crea empresas, usuarios empresariales, datos bancarios, transportes y rutas.
- `OperacionHistoricaDemoSeeder`: genera programaciones, tarifas, clientes, reservas pagadas, viajeros, pasajes y pagos.

## Volumen predeterminado

- 50 agencias de autobuses.
- Cuatro autobuses por agencia.
- Cinco rutas como máximo por agencia.
- Cuatro programaciones diarias por agencia desde el 1 de enero de 2026 hasta el día de ejecución.
- 15 pasajes pagados por cada programación de autobús.
- 100 conductores de carro.
- Un carro de cuatro puestos físicos por conductor: un puesto para el conductor y tres puestos comercializables.
- Una ruta directa por conductor, sin paradas intermedias.
- Tres programaciones semanales por conductor: lunes, miércoles y viernes.
- Tres pasajes pagados por cada programación de carro.

Cada programación genera una reserva pagada perteneciente a un cliente diferente. La reserva contiene los 15 pasajeros del autobús o los tres pasajeros del carro. Las programaciones anteriores al día actual quedan finalizadas y sus pasajes se marcan como abordados, salvo una muestra pequeña de inasistencias.

Al ejecutar el 27 de septiembre de 2026, el volumen esperado es de 65.600 programaciones y reservas, y 844.800 pasajes.

## Ejecución

```bash
php artisan migrate:fresh --seed
```

La carga histórica utiliza inserciones por lotes para evitar ejecutar el flujo completo del checkout cientos de miles de veces. Está diseñada para una base reconstruida desde cero. Si ya encuentra programaciones históricas, omite esa parte para evitar duplicados.

Para pruebas técnicas reducidas se pueden definir temporalmente `DEMO_TOTAL_AGENCIAS`, `DEMO_TOTAL_CONDUCTORES`, `DEMO_FECHA_DESDE` y `DEMO_FECHA_HASTA`. Si no se definen, siempre se utiliza el volumen predeterminado indicado anteriormente.
