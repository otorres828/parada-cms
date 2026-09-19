# Esquema de Parada

Las tablas existentes admins, users, sections, permissions y permission_admin conservan sus nombres. ROOTS corresponde a Admin; USUARIOS corresponde a User. Las tablas de operación mantienen los nombres del diagrama inicial.

La ampliación administrativa incorpora access_roles, access_role_permission, configuraciones, pagos, retiros, reembolsos, movimientos y auditorias. Admin y UsuarioEmpresa pueden pertenecer a un rol; los roles de empresa están asociados a una empresa.

Las claves foráneas se declaran por separado con unsignedBigInteger y foreign. Las tablas operativas iniciales conservan la cascada solicitada. Los registros contables restringen el borrado de sus padres para conservar el historial. La deshabilitación se realiza con status/estatus.

Los importes utilizan decimal(12,2); los cálculos de saldo y comisiones utilizan bcmath. Los nuevos módulos financieros operan en USD. Las contraseñas se convierten a hash y se ocultan al serializar.

Véase docs/modulos-admin.md para los flujos implementados, la instalación y las comprobaciones.
