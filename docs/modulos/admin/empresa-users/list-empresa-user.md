# ListEmpresaUser

Lista usuarios de la empresa recibida en `empresa_id`, validada al entrar. Incluye búsqueda, estado activo/inactivo, ordenación y paginación.

Permisos: empresas.users.list para entrar, empresas.users.add para Nuevo registro y empresas.users.edit para edición o cambio de estado. Nuevo registro comparte la fila del buscador y filtros. Las acciones se restringen a la empresa y el cambio de estado se audita.

Ya no incluye botones ni rutas de detalle o permisos independientes; los permisos se gestionan en SaveEmpresaUser. Las alertas usan `admin_usuario_empresa_success`: flash al redirigir, dispatch al cambiar estado en pantalla.
