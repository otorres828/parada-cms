# Transporte: empresas, vehículos y reservas

- `empresas.tipo_entidad`: `agencia_autobus` (Agencia de Autobús) o `conductor_carro` (Conductor de carro). Se edita en el formulario de empresa y aparece en listado y detalle.
- Modelo `Transporte`, tabla `transportes`, relación `Programacion::transporte()` y FK `programaciones.transporte_id`.
- Amenidades: modelo `AmenidadTransporte`, tabla `amenidad_transporte`, FK `transporte_id`. Se muestran en el detalle del transporte.
- `transportes.tipo_transporte`: `autobus` o `carro`. El tipo pertenece al vehículo, independientemente del tipo de entidad de su empresa. Los transportes existentes reciben `autobus` como valor inicial. El módulo administrativo sigue siendo de consulta; los consumidores que registran vehículos deben enviar este atributo.
- Rutas: `admin.transportes.list` y `admin.transportes.detail`, con parámetro `transporte_id`. URI `/admin/transportes` y `/admin/transportes/detalle/{transporte_id}`. Permisos, menú y seeders utilizan `transportes`.
- Listado de transporte y reportes de ventas/empresas: filtro `tipo_transporte`; vacío muestra ambos. El filtro de reportes también se aplica a Excel.
- Reservas: columna y descripción de tipo; también en pasajes y reembolsos. Las órdenes de cobro congelan el tipo por reserva y muestran los tipos incluidos, admitiendo órdenes mixtas. La migración completa ese dato en las órdenes existentes sin recalcular sus importes.
- Códigos nuevos: `AU-` o `CA-`, seguidos de diez caracteres alfanuméricos mayúsculos. Se comprueba que no exista la referencia y se mantiene la restricción única de la base. Al reiniciar una reserva NUEVA con formato antiguo se genera un código del formato nuevo. No se modifican las referencias de compras históricas pagadas o pendientes.

## Migración

`2026_09_27_000001_convert_autobuses_to_transportes` renombra tablas, columnas y claves foráneas conservando IDs, datos y relaciones; actualiza permisos sin cambiar sus IDs. Las migraciones históricas mantienen los nombres originales para que tanto bases nuevas como existentes recorran la misma conversión.

En otro entorno ejecutar `php artisan migrate` y limpiar cachés de rutas/vistas al desplegar. La reversión conserva registros y relaciones, pero elimina los nuevos campos de clasificación.

## Verificación

`php tests/TransportesSmoke.php` requiere PHP 8.3+, BCMath y PDO SQLite. Ejecuta en memoria el flujo de reservas, filtros de ambos tipos, totales de reportes, prefijos, amenidades, órdenes mixtas, renderizado de pantallas Livewire y migración de ida/vuelta con datos. `tests/ExchangeRateSmoke.php` verifica las tasas de cambio existentes.
