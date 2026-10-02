# SaveDatoBancario

Alta/edición de cuentas propias con permisos datos-bancarios/add y edit. El ID está Locked y todas las cargas/escrituras se limitan por empresa_id de la sesión.

Campos: tipo (pago móvil o cuenta bancaria), banco, titular, tipo de titular, documento, cuenta o teléfono, tipo de cuenta y estatus. Cuenta bancaria exige 20 dígitos y corriente/ahorro; pago móvil exige teléfono nacional de 11 dígitos y limpia tipo_cuenta. Valida en Alpine y servidor con mensajes en español. Al guardar permanece en pantalla y notifica éxito.

El catálogo está en config/bancos.php, basado en el [directorio bancario de SUDEBAN](https://sudeban.gob.ve/index.php/bancario/), consultado el 2 de octubre de 2026. Es un catálogo local que debe actualizarse cuando cambie el directorio; no consulta servicios externos al abrir el formulario. No implica que todas las instituciones ofrezcan pago móvil.
