# Admin\Access

## Admin\Access

`allows()` consulta los permisos activos del administrador autenticado. `authorize()` responde con HTTP 403 cuando el permiso no existe. `permissions()` devuelve las combinaciones activas de sección y acción, considerando también el estado del grupo, sección, permiso y asignación.

Las rutas GET se protegen mediante `CheckPermission`. Las acciones que mutan información deben mantener autorización en el backend.

Fuente: `app/Services/Admin/Access.php`. El servicio usa el guard `admin`; la decisión de `allows()` se delega a `Admin::hasPermission()`.

## Decisión paso a paso

1. allows recupera el administrador del guard admin; si no existe devuelve false.
2. hasPermission delega en checkPermissionsBatch del modelo.
3. Una cuenta inactiva obtiene false; root activo obtiene acceso completo.
4. Superadministradores activos pueden acceder excepto a la sección admins.
5. Los demás acceden a account y a las combinaciones presentes en su mapa de permisos, salvo admins.
6. authorize transforma false en HTTP 403.

permissions devuelve las asignaciones activas de la base de datos; por sí solo no representa los privilegios especiales de root o superadministrador.
