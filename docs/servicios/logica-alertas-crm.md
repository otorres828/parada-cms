# Alertas del CRM

Cada módulo utiliza eventos y claves de sesión propios, diferenciados por panel: por ejemplo `admin_amenidad_success` y `empresas_campana_success`. Los errores usan el mismo prefijo con `_error`.

Cuando una acción permanece en su vista, la clase emite `dispatch` y Alpine muestra el toast. Cada listener se elimina en `destroy` para evitar acumulación durante navegación Livewire.

Cuando una acción redirige al listado, la clase guarda una sesión flash y redirige. Solo el listado consume esa clave con `session()->pull`; su `init` muestra el toast directamente, sin volver a emitir un evento global. Los formularios Save y los detalles no consumen las sesiones del listado. Una acción sin redirección no debe guardar además un flash.

El flash permite transportar el mensaje entre peticiones. Un dispatch emitido en la vista de origen no garantiza que el listado destino tenga su listener listo.
