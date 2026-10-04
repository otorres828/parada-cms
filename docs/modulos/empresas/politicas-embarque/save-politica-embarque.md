# SavePoliticaEmbarque

Formulario directo de políticas de embarque y desembarque para la empresa autenticada. La ruta empresas.politicas-embarque.edit está protegida por el permiso politicas-embarque/edit. Extiende EmpresaComponent y conserva el orden mount, render, save.

Mount carga empresas.politicas en contenido. La vista reutiliza el editor TinyMCE del contenido legal de Admin, con altura adaptada a la pantalla. Permite títulos, listas, enlaces y tablas. Guardar autoriza edit, valida contenido obligatorio de hasta 60000 caracteres y actualiza únicamente empresas.politicas de la empresa autenticada. No necesita tablas ni archivos JSON nuevos; usa el campo existente de cada empresa. Permanece en el formulario y emite successEventList. El listener se libera al navegar.

El enlace de Políticas de embarque del menú empresarial usa navigate=false, como las páginas legales de Admin. Se abre con un href normal y carga completa para inicializar TinyMCE. El componente conserva su registro local @script y libera la instancia al abandonar la vista.
