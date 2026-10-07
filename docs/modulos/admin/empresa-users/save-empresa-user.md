# SaveEmpresaUser

Crea o edita usuarios de una empresa desde Admin. Las rutas `admin.empresas.users.add` y `admin.empresas.users.edit` reciben empresa_id, y la edición también usuario_empresa_id. Se exigen los permisos `empresas.users.add` o `empresas.users.edit` al guardar y en el middleware de entrada.

`mount` valida la empresa, carga una sola vez los grupos activos y delega las asignaciones de edición a `editar`. El usuario se consulta siempre dentro de esa empresa y excluyendo eliminados; los IDs de contexto están bloqueados.

Reutiliza `x-empresa-users.form`, el mismo formulario del panel Empresas: nombre, correo único, contraseña (mínimo ocho caracteres, opcional al editar), switch para mostrarla, estado activo/inactivo y permisos agrupados por sección. Volver al listado se encuentra en el encabezado.

La diferencia de Admin es el switch Administrador de empresa. Activado oculta los permisos y concede es_admin = 1; apagado muestra la selección y guarda es_admin = 0. Al guardar como administrador se retiran las asignaciones individuales. Al guardar un usuario normal valida permisos existentes y activos, incluidos grupo y sección. Usuarios es una sección reservada al administrador empresarial y no se puede asignar individualmente.

Guarda datos y permisos en una transacción y registra auditoría con los IDs asignados y la bandera es_admin. El cast hashed guarda la contraseña; una contraseña vacía conserva el hash anterior. Al cambiarla renueva remember_token. Redirige al listado con `admin_usuario_empresa_success`, consumido solo allí.

La gestión de permisos forma parte del alta y edición; ya no existen pantallas separadas de detalle o permisos.
