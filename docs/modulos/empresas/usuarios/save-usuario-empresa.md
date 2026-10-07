# SaveUsuarioEmpresa

Formulario de alta y edición de usuarios empresariales siguiendo `SaveAdmin`: campos de credenciales de ancho limitado, contraseña visible opcionalmente, estado y permisos agrupados por sección con selección de todos.

Rutas: `empresas.usuarios.add` y `empresas.usuarios.edit`, esta última con `usuario_empresa_id`. Extiende `EmpresaComponent` y exige un usuario activo con `es_admin = 1` en cada petición y al guardar. Los registros ajenos o eliminados responden 404. El identificador de edición está bloqueado.

`mount` carga una vez los grupos, secciones y permisos activos. Al editar consulta el usuario y delega las asignaciones a `editar`. Los métodos siguen el orden mount, render, save y auxiliares.

Campos: nombre obligatorio, correo único entre usuarios de empresa, contraseña de 10 a 255 caracteres (opcional al editar) y estado activo/inactivo. La contraseña se guarda mediante el cast hashed y se renueva remember_token al cambiarla. No se puede desactivar la propia cuenta.

Las altas fuerzan `es_admin = 0` y la empresa se obtiene de la sesión. El formulario no tiene un selector de administrador. Al editar conserva el valor es_admin existente; las cuentas ya administradoras mantienen el acceso completo y no muestran selección de permisos.

Los usuarios normales reciben permisos activos mediante la relación permisos, dentro de una transacción. Se rechazan IDs duplicados, inexistentes o de jerarquías inactivas. La sección Usuarios está reservada al administrador y no puede asignarse desde este formulario.

El guardado usa session flash `empresas_usuario_success` y redirige al listado; el formulario no consume esa sesión, evitando alertas duplicadas. Incluye Volver al listado sin condicional de permiso.

Las pruebas de `tests/UsuariosEmpresaSmoke.php` cubren altas, edición y conservación de contraseña, permisos, estados, aislamiento y rechazo de usuarios sin es_admin.
