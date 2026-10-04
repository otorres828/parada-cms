# SaveTransporte

Alta de transportes en empresas.transportes.add, protegida por el permiso transportes/add. Extiende EmpresaComponent. Mount carga las amenidades activas como Collection de Eloquent y el permiso para volver al listado.

El formulario solicita placa, modelo, tipo de asiento, capacidad (1 a 100), estatus 1 activo o 2 inactivo y amenidades opcionales. La placa se normaliza a mayúsculas y debe ser única. La empresa y el tipo de transporte se obtienen del usuario autenticado, sin select ni campos manipulables. Los carros proponen 4 puestos y los autobuses 40; la capacidad es editable. No crea transportes plantilla.

Guardar vuelve a autorizar add y comprueba que cada amenidad siga activa. Transporte y relaciones de amenidades se registran en una transacción. Si puede listar, vuelve al listado con alerta; de lo contrario limpia los campos de identificación y amenidades y muestra la confirmación en el mismo formulario. También atiende empresas.transportes.edit con permiso edit y un ID protegido con Locked. La edición busca un transporte propio del tipo de la empresa y excluye plantillas. Carga los datos y amenidades activas; guardar sincroniza la selección. La capacidad del transporte es editable aunque tenga programaciones; estas conservan asientos_totales como copia de la capacidad al crearse. La unicidad de placa excluye el mismo registro al editar.
