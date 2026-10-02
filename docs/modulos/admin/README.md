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

- **Empresas:** administra identidad, contrato y fechas de cobranza de agencias y conductores; su detalle consulta políticas y datos bancarios.
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



## Documentación por clase Livewire

Cada clase tiene su propio documento, agrupado por su módulo. Listados, formularios, detalles y pantallas especiales explican sus acciones reales por separado.

### Dashboard

- [Dashboard](dashboard.md)

### account

- [Password](account/password.md)
- [Profile](account/profile.md)

### admins

- [ListAdmin](admins/list-admin.md)
- [SaveAdmin](admins/save-admin.md)

### amenidades

- [ListAmenidad](amenidades/list-amenidad.md)
- [SaveAmenidad](amenidades/save-amenidad.md)

### auditoria

- [DetailAudit](auditoria/detail-audit.md)
- [ListAudit](auditoria/list-audit.md)

### auth

- [Login](auth/login.md)

### clientes

- [DetailCliente](clientes/detail-cliente.md)
- [ListCliente](clientes/list-cliente.md)
- [SaveCliente](clientes/save-cliente.md)

### cupones

- [DetailCampana](cupones/detail-campana.md)
- [ListCampana](cupones/list-campana.md)
- [SaveCampana](cupones/save-campana.md)

### empresa-users

- [DetailEmpresaUser](empresa-users/detail-empresa-user.md)
- [ListEmpresaUser](empresa-users/list-empresa-user.md)
- [PermissionEmpresaUser](empresa-users/permission-empresa-user.md)
- [SaveEmpresaUser](empresa-users/save-empresa-user.md)

### empresas

- [DetailEmpresa](empresas/detail-empresa.md)
- [ListEmpresa](empresas/list-empresa.md)
- [PoliticasEmpresa](empresas/politicas-empresa.md)
- [SaveEmpresa](empresas/save-empresa.md)

### exoneraciones-tasa-servicio

- [ListExoneracionTasaServicio](exoneraciones-tasa-servicio/list-exoneracion-tasa-servicio.md)
- [SaveExoneracionTasaServicio](exoneraciones-tasa-servicio/save-exoneracion-tasa-servicio.md)

### legales

- [ContenidoPagina](legales/contenido-pagina.md)
- [EmpresaDocumento](legales/empresa-documento.md)
- [ListDocumentos](legales/list-documentos.md)

### ordenes-cobro

- [DetailOrdenCobro](ordenes-cobro/detail-orden-cobro.md)
- [ListOrdenCobro](ordenes-cobro/list-orden-cobro.md)

### pasajes

- [DetailPasaje](pasajes/detail-pasaje.md)
- [ListPasaje](pasajes/list-pasaje.md)

### preguntas-frecuentes

- [ListCategoria](preguntas-frecuentes/list-categoria.md)
- [ListPregunta](preguntas-frecuentes/list-pregunta.md)
- [SaveCategoria](preguntas-frecuentes/save-categoria.md)
- [SavePregunta](preguntas-frecuentes/save-pregunta.md)

### programaciones

- [ListProgramacion](programaciones/list-programacion.md)
- [PassengerProgramacion](programaciones/passenger-programacion.md)

### reembolsos

- [DetailReembolso](reembolsos/detail-reembolso.md)
- [ListReembolso](reembolsos/list-reembolso.md)

### reportes

- [CompaniesReport](reportes/companies-report.md)
- [ExchangeRates](reportes/exchange-rates.md)
- [SalesReport](reportes/sales-report.md)

### reservas

- [DetailReserva](reservas/detail-reserva.md)
- [ListReserva](reservas/list-reserva.md)

### solicitudes

- [DetailSolicitud](solicitudes/detail-solicitud.md)
- [ListSolicitud](solicitudes/list-solicitud.md)

### tasas-servicio

- [ListTasaServicio](tasas-servicio/list-tasa-servicio.md)
- [SaveTasaServicio](tasas-servicio/save-tasa-servicio.md)

### terminales

- [ListTerminal](terminales/list-terminal.md)
- [SaveTerminal](terminales/save-terminal.md)

### transportes

- [DetailTransporte](transportes/detail-transporte.md)
- [ListTransporte](transportes/list-transporte.md)

### viajes

- [DetailViaje](viajes/detail-viaje.md)
- [ListViaje](viajes/list-viaje.md)
