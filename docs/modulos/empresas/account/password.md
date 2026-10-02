# Password

Extiende EmpresaComponent y usa su usuario autenticado. La vista propia solicita contraseña actual, nueva contraseña y confirmación, con validación Alpine y PHP.

save valida la contraseña actual con el guard empresa y exige una contraseña confirmada de 10 a 255 caracteres. Guarda únicamente la contraseña del usuario autenticado; el cast hashed de UsuarioEmpresa se encarga del hash. En la misma transacción renueva remember_token y elimina las otras sesiones de ese usuario de empresa, conservando la sesión actual y las de otros usuarios o tipos de cuenta.

Al completar, limpia los tres campos y muestra la alerta de éxito. Mi cuenta no requiere permisos de módulo adicionales.
