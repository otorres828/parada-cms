# ListUsuarioEmpresa

Sustituye la pantalla inicial `ListUsuario`. La ruta `empresas.usuarios.list` permanece igual y muestra exclusivamente los usuarios no administradores de la empresa autenticada. El filtro existente `es_admin` excluye las cuentas con `es_admin = 1` desde la consulta, antes de paginar.

Solo los usuarios activos con `es_admin = 1` pueden entrar. La restricción se aplica en el middleware, en la autorización del modelo y al iniciar cada petición Livewire del componente. Un permiso asignado a un usuario normal no habilita este módulo; el menú también lo oculta.

Incluye búsqueda por ID, nombre o correo, filtro de estado, ordenación y paginación. Excluye eliminados por defecto. Reutiliza los componentes de listado y ofrece Nuevo registro, Editar y Cambiar estado. Las acciones verifican pertenencia a la empresa en el servidor. El cambio de estado excluye las cuentas administradoras y no permite desactivar la propia cuenta.

Los cambios de estado emiten `empresas_usuario_success` permaneciendo en pantalla. Después de guardar un usuario, el listado consume una sola vez la sesión flash del mismo nombre.

Se comprueba en `tests/UsuariosEmpresaSmoke.php`, con SQLite en memoria.

Eliminar utiliza el componente delete-button y una confirmación SweetAlert. La acción deleteUsuario exige acceso de administrador, verifica empresa y excluye administradores, bloquea el registro y coloca estatus = 0. Conserva el registro para mantener referencias históricas, impide su acceso y deja de mostrarlo en el listado. Reinicia la paginación y emite empresas_usuario_success.
