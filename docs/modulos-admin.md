# Módulos administrativos de Parada

## Estructura

Las rutas permanecen en routes/admin.php y usan Route::livewire. Los componentes viven en app/Livewire/Admin y usan layouts.cms; el login conserva layouts.auth. Cada componente conserva su archivo y vista. ModulePage, SettingsPage, AccountPage y ReportPage comparten la lógica repetida, y config/admin_modules.php define los campos, validación, columnas y relaciones de cada módulo.

Las consultas de listados y reportes comienzan en Model::searchAdmin. Los filtros y ordenación se conservan en queryString. La paginación admite 10, 25, 50 o 100 filas. Los selectores de relaciones permiten buscar opciones.

## Operación

- Empresas: alta, edición, habilitación, comisión y habilitación de retiros. Sus colaboradores se administran desde el detalle de cada empresa.
- Administradores: alta y edición por dueños de plataforma. Los colaboradores reciben permisos directos y/o un rol.
- Roles: ámbito administrativo o de una empresa específica. No se permite asignar a un colaborador de empresa un rol perteneciente a otra empresa.
- Catálogos: secciones, permisos, estados, terminales y amenidades.
- Viajes, salidas, autobuses, clientes, reservas y pasajes: supervisión. Los viajes y las ventas se originan en los otros paneles/sitios.
- Campañas: configuración, activación y generación única de hasta 1.000 códigos de un solo uso por campaña. Descuentos fijos USD o porcentuales. Las condiciones principales se conservan después de generar códigos.
- Reportes: ventas por fecha, ventas por empresa, movimientos financieros y ocupación. Exportación CSV.
- Auditoría: registro del actor y de los cambios realizados desde estos módulos, con exclusión de contraseñas y datos bancarios del contenido registrado.
- Mi cuenta: edición del perfil y contraseña con validación de contraseña actual.

## Finanzas en USD

El módulo Pagos concilia dinero ya recibido; no ejecuta cobros. Se selecciona una reserva que ya tenga estado pagado y se registra la referencia de recepción. Solo se permite un pago por reserva. Los montos provienen de la reserva, no de campos editables en el formulario.

El neto de la empresa es monto_pasajes menos descuento_aplicado y menos la comisión configurada. La tasa de servicio pertenece a la plataforma. La comisión se fija en el momento de registrar el pago.

El saldo contable se obtiene de movimientos inmutables, acreditados al conciliar pagos. Las reservas históricas pagadas que todavía no se hayan conciliado aparecen en las ventas, pero no incrementan ese saldo. No se deben crear pagos ficticios para igualar el dashboard.

Retiros y reembolsos siguen el ciclo pendiente → aprobado → pagado, o rechazo desde pendiente/aprobado. Los importes pendientes/aprobados se reservan para impedir que otras solicitudes gasten los mismos fondos. Las transacciones bloquean la empresa y el registro al modificar saldos. Reintentar una confirmación no genera otro movimiento.

Confirmar un retiro o reembolso significa registrar una transferencia externa ya realizada. Se exige referencia y comprobante privado PDF/JPG/PNG, máximo 5 MB. La aplicación no llama a bancos ni a pasarelas. Los reembolsos implementados son totales; al confirmarlos la reserva cambia a reembolsado. La gestión de disponibilidad/asientos y la validación QR del sitio de ventas deben respetar ese estado; este panel no cambia asientos automáticamente.

Las empresas se deshabilitan, no se eliminan físicamente. Las claves financieras usan restricción de borrado para conservar la contabilidad.

## Permisos

check.permisos protege las rutas. Las acciones Livewire vuelven a verificar permisos con el usuario actual. Un permiso oculto en el menú no sustituye esta validación. Los dueños mantienen acceso total; solo ellos delegan permisos y administran roles, secciones, permisos y niveles de los administradores.

El login verifica el hash de la contraseña y limita intentos. Se eliminó el acceso por contraseña universal. El cierre de sesión usa POST con CSRF.

## Instalación y comprobación

Se agregaron dos migraciones y se actualizaron los JSON de permisos. En otra instalación:

- php artisan migrate
- php artisan db:seed --class=SectionSeeder
- php artisan db:seed --class=PermissionSeeder

Se requiere PHP 8.3 o superior y bcmath. Las pruebas utilizan SQLite en memoria con pdo_sqlite, sin modificar los datos reales:

- php tests/schema-smoke.php
- php tests/admin-menu-smoke.php
- php tests/admin-http-smoke.php

admin-http-smoke incluye las pruebas de dashboard, 80 rutas de componentes, formularios, finanzas, permisos, exportaciones y layout HTTP.
