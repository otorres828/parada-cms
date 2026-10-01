# Acceso y auditoría administrativa

## Admin\Access

`allows()` consulta los permisos activos del administrador autenticado. `authorize()` responde con HTTP 403 cuando el permiso no existe. `permissions()` devuelve las combinaciones activas de sección y acción, considerando también el estado del grupo, sección, permiso y asignación.

Las rutas GET se protegen mediante `CheckPermission`. Las acciones que mutan información deben mantener autorización en el backend.

## Admin\Audit

`record()` guarda administrador, acción, entidad, ID, IP y datos adicionales. Antes de persistir elimina claves sensibles conocidas, entre ellas contraseñas, tokens, secretos, comprobantes y datos bancarios.
