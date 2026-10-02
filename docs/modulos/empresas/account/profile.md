# Profile

El perfil de Empresas extiende EmpresaComponent y utiliza su usuario autenticado, inicializado en boot. En mount carga nombre y email; la vista propia permite editarlos y solicita la contraseña actual.

La acción save valida la contraseña contra el guard empresa y la unicidad del correo en usuarios_empresa, excluyendo al propio usuario. Solo actualiza nombre y email del usuario autenticado; no modifica la empresa, permisos ni datos de Admin. Al guardar limpia la contraseña y emite la alerta de éxito sin redirigir. El formulario conserva el validador Alpine.

Mi cuenta está disponible para todos los usuarios autenticados de empresa, sin permisos de módulo adicionales.
