# Módulos del panel administrativo

El panel administrativo permite supervisar la plataforma, configurar reglas comerciales y consultar la operación de las empresas. Las rutas están protegidas por `auth:admin` y `check.permisos`. La visibilidad del menú no reemplaza la autorización del servidor.

## Dashboard

Presenta ventas, tasas de servicio, reservas, pasajes, empresas y próximas salidas dentro del período seleccionado. Permite usar períodos predefinidos o un rango de fechas personalizado y filtrar por tipo de transporte.

## Administración

- **Administradores:** administra las cuentas internas y sus permisos.
- **Tasa de servicio:** configura los importes fijos o porcentuales que paga el cliente por cada pasaje.
- **Exoneraciones de tasa:** define períodos durante los cuales una empresa no genera tasa de servicio.
- **Auditoría:** consulta las acciones administrativas registradas por el sistema.

## Empresas y clientes

- **Empresas:** administra agencias de autobuses y conductores de carro, su contrato, fechas de cobranza, políticas y datos bancarios.
- **Usuarios de empresa:** consulta y gestiona los usuarios vinculados a una empresa y sus permisos.
- **Clientes:** consulta clientes, sus reservas y viajeros frecuentes.

## Operación de viajes

- **Rutas de viaje:** consulta el recorrido, sus terminales ordenados y los tramos comerciales.
- **Programaciones:** consulta salidas, pasajeros y disponibilidad por tramo. Una programación finalizada no vuelve a otro estado.
- **Transportes:** consulta autobuses y carros, capacidad, amenidades e historial de programaciones. Véase [Transportes](transportes.md).

## Ventas y finanzas

- **Reservas:** consulta el comprador, origen y destino contratados, pasajes, descuentos, tasas, pagos y reprogramaciones.
- **Pasajes:** consulta el snapshot del viajero, asiento, importes, abordaje y QR cuando corresponde.
- **Reembolsos:** el administrador de plataforma supervisa los reembolsos gestionados por las empresas.
- **Órdenes de cobro:** controla las tasas de servicio que deben transferir las empresas cuyo contrato indica que ellas reciben el dinero.

## Promociones

- **Cupones:** configura campañas, modalidad, alcance por reserva o por pasaje, tipo de descuento y códigos disponibles.

## Reportes

- **Ventas:** resume la operación por período y tipo de transporte.
- **Empresas:** agrupa resultados comerciales por empresa.
- **Tasas de cambio:** consulta y actualiza las tasas monetarias usadas por la plataforma.

## Legales

- **Documentos:** administra los expedientes y archivos de cada empresa.
- **Contenido de páginas:** mantiene Sobre nosotros, privacidad, cookies y términos en archivos JSON.

El almacenamiento y las reglas se detallan en [Contenido legal y preguntas frecuentes](contenido-legal-y-preguntas-frecuentes.md).

## Catálogos

- **Terminales:** administra ubicaciones, estado geográfico y coordenadas.
- **Amenidades:** administra las características disponibles para los transportes.
- **Preguntas frecuentes:** mantiene categorías y artículos breves para el centro de ayuda del sitio de venta.

## Soporte

- **Contacto:** atiende las solicitudes enviadas por empresas interesadas desde el formulario público y permite eliminar spam.

## Mi cuenta

Todo administrador autenticado puede actualizar su perfil y contraseña. Estas operaciones no dependen de permisos de módulos.

