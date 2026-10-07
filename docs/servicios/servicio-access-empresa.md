# Access del panel de Empresas

Clase: `app/Services/Empresa/Access.php`. Usa el guard `empresa`, cuyo proveedor es `UsuarioEmpresa`; no utiliza cuentas ni permisos de Admin.

## Métodos

- `allows($module, $action)`: recupera el usuario autenticado por el guard `empresa` y consulta su permiso. Devuelve `false` si no hay usuario.
- `authorize($module, $action)`: aplica la misma comprobación y responde con HTTP 403 cuando no está autorizado.
- `permissions($usuario)`: devuelve los pares `section_url` / `permission_url` asignados al usuario, siempre que grupo, sección y permiso estén activos.

La consulta enlaza `permissions_empresa`, `sections_empresa`, `groups_empresa` y `permisos_usuarios_empresa`. La tabla de asignaciones usa `usuario_empresa_id` y `permiso_id`; no tiene columna de estado: retirar una asignación implica eliminar esa relación.

## Reglas del usuario

`UsuarioEmpresa::checkPermissionsBatch()` rechaza usuarios cuyo `estatus` no sea activo. Si `es_admin = 1`, concede acceso administrativo al panel de su empresa. No existen niveles root o superadmin. Los demás usuarios acceden mediante permisos asignados; Mi cuenta no requiere un permiso de módulo.

`permisos()` y `permissions()` apuntan a la misma relación empresarial. `isAdmin()` comprueba el valor `1`, y `getPermissionsMap()` devuelve los permisos activos asignados.

Estos métodos controlan acciones, no la pertenencia de los registros. Cada módulo debe limitar sus consultas y modificaciones a la empresa del usuario autenticado.

## Middleware y registro de rutas

`CheckPermissionEmpresa` usa nombres `empresas.*` y el guard `empresa`. El acceso anónimo se dirige a `empresas.login`. Perfil y contraseña solo exigen autenticación y usuario activo; Dashboard exige `dashboard/list`. Una ruta protegida sin entrada en el mapa se rechaza, incluso para administradores de empresa.

Cuando falta un permiso, el middleware redirige al dashboard si está permitido; en caso contrario, al perfil. El catálogo incluye módulos futuros, pero sus rutas se incorporarán al mapa cuando se implementen.

## Catálogo y seeder

`GroupSectionPermissionEmpresaSeeder` lee `storage/json/grupo_seccion_permiso_empresa.json` y se ejecuta inmediatamente después del seeder de permisos de Admin.

1. Valida la estructura del JSON y URLs únicas de grupos y secciones.
2. En una transacción, actualiza grupos por ID estable.
3. Actualiza secciones por URL y las vincula a su grupo.
4. Actualiza permisos por sección y URL, sin cambiar sus IDs existentes.
5. Elimina permisos que ya no estén declarados dentro de una sección procesada. Las asignaciones a esos permisos se eliminan por la clave foránea.

Repetir el seeder conserva las asignaciones a permisos que permanecen en el catálogo. Como el seeder de Admin, no elimina automáticamente grupos o secciones ausentes del JSON. Los catálogos de permisos usan `status` booleano, independiente del `estatus` del usuario.

El catálogo inicial tiene 7 grupos y 15 secciones. No incorpora permisos para Mi cuenta. Definir un permiso en el JSON no crea la pantalla ni una ruta pública.

## Verificación

`tests/PermisosEmpresaSmoke.php` ejecuta pruebas con SQLite en memoria: repetición del seeder, conservación de asignaciones, administrador e inactividad, jerarquía de permisos, aislamiento de asignaciones entre usuarios, redirecciones y rechazo de rutas sin mapa. No ejecuta `DatabaseSeeder` ni altera los datos locales.

El módulo Usuarios es exclusivo de usuarios activos con `es_admin = 1`. Los permisos explícitos de la sección `usuarios` no habilitan a los usuarios normales. Esta regla se aplica al menú, middleware y acciones de los componentes ListUsuarioEmpresa y SaveUsuarioEmpresa. Las altas desde Empresas siempre crean usuarios sin es_admin.
