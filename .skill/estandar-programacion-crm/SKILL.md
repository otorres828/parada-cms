---
name: estandar-programacion-crm
description: Crear, modificar o revisar módulos del CRM Laravel y Livewire de Rodando CMS respetando el estilo de Oliver en clases, modelos, permisos, rutas, listados, detalles, componentes Blade, Alpine, formularios y documentación. Usar también al adaptar módulos de Admin al panel de Empresas.
---

# Estándar de programación del CRM

Conservar el estilo del propietario del proyecto: módulos fáciles de localizar, clases con responsabilidades concretas, vistas legibles y componentes separados por módulo. Este skill recoge sus preferencias explícitas y los patrones revisados en las 56 clases de `app/Livewire/Admin`, sus vistas y componentes asociados.

Las rutas mencionadas en el texto son relativas a la raíz del repositorio. Las referencias enlazadas pertenecen a este skill.

## Cómo aplicarlo

1. Revisar el módulo equivalente existente, su clase, vista, componentes, modelo y autorización antes de editar. Usar los documentos en `docs/modulos/admin` como orientación y comprobar el código vigente.
2. Para clases, consultas, servicios, rutas o permisos, leer [Backend y módulos](references/backend-y-modulos.md).
3. Para listados, detalles, maquetación, componentes y comentarios, leer [Vistas y componentes](references/vistas-y-componentes.md).
4. Para formularios, validación del navegador, eventos, mapas o editores, leer [Alpine y formularios](references/alpine-y-formularios.md).
5. Implementar únicamente lo necesario para el requerimiento y verificar el comportamiento afectado. No convertir una corrección puntual en una reescritura global.

## Reglas esenciales

- Separar `List*`, `Save*` y `Detail*` cuando existan esas pantallas. No crear un CRUD completo si el módulo solo necesita consulta.
- Clases en `app/Livewire/{Panel}/{Modulo}`, vistas en `resources/views/livewire/{panel}/{modulo}` y componentes del dominio en `resources/views/components/{modulo}`.
- Tipar las propiedades propias de las clases y los métodos cuando su contrato esté definido. Respetar los tipos exigidos por Laravel/Livewire en propiedades heredadas.
- Usar 4 espacios, UTF-8, finales LF y salto final conforme a `.editorconfig`. Mantener bloques espaciados y legibles, sin líneas vacías repetidas ni espacios al final.
- Validaciones y arrays de datos: un atributo y su valor por línea. Consultas largas: métodos en el modelo correspondiente. En callbacks PHP de consultas usar `function ($query) { ... }`, con llaves, en lugar de `fn`.
- El middleware valida el acceso a pantallas. En `mount` calcular los permisos de presentación; no volver a autorizar allí la misma entrada ya protegida. Las acciones de escritura y descarga conservan su autorización en el servidor.
- Consultar permisos en la clase, no dentro de Blade. Los botones y enlaces reciben booleanos como `canAdd`, `canEdit`, `canDetail` o permisos específicos.
- Ordenar menú, rutas, imports de Livewire y mapa de permisos siguiendo los mismos grupos y módulos. No reorganizarlos alfabéticamente ni inventar un orden nuevo.
- Estados de catálogo: `estatus`, `1` activo, `2` inactivo y `0` eliminado. Reutilizar `ModelHelper::ESTADO_ACTIVE`, `ESTADO_INACTIVE` y `ESTADO_DELETE`; no duplicarlos en cada modelo. Mantener los nombres existentes `status` y estados de negocio específicos cuando corresponda.
- En `searchAdmin`, sin filtro de estado se excluyen eliminados cuando el modelo usa esa convención. Un estado numérico no se convierte a booleano.
- Listados con búsqueda, ordenación y paginación reutilizan los traits/componentes existentes. Formularios reutilizan el validador Alpine y mantienen validación PHP.
- Las tablas grandes y los `card-body` descriptivos de los detalles se extraen a componentes por módulo, con `@props` explícitos.
- Cada vista principal comienza con un comentario Blade en español que explica lo que hace y enumera los componentes que realmente utiliza.
- Buscador y botón Nuevo comparten fila. Un único select puede ir junto al buscador en esa fila; varios filtros usan columnas. Excel queda a la derecha y al nivel de los filtros.
- Mantener cada servicio centrado en su dominio. No concentrar cupones, pagos, viajeros y reservas en un solo servicio ni agregar wrappers sin responsabilidad propia.

## Alcance y excepciones

Este proyecto es el CRM. El sitio donde el cliente compra pasajes pertenece a otro proyecto: no agregar aquí pantallas públicas de compra, APIs o controladores para ese sitio sin un requerimiento explícito.

El código actual contiene diferencias respecto del estándar solicitado: propiedades sin tipar, validaciones `0/1`, consultas largas y callbacks abreviados. No tratarlas como plantillas para código nuevo. Tampoco aprovechar una tarea de documentación o estilo para corregir comportamientos ajenos a su alcance.

Los nombres de archivos pueden cambiar. Al revisar este skill, el middleware administrativo se llama `CheckPermissionAdmin.php`, no `CheckPermission.php`. Comprobar la configuración actual antes de adaptar referencias antiguas.

Para el panel de Empresas, aplicar el mismo estilo pero verificar guard, modelo autenticable, middleware, rutas, permisos y alcance por `empresa_id`. No copiar literalmente los elementos `admin` de una clase existente. Un archivo duplicado que aún apunta al guard de Admin no constituye un estándar válido.

## Documentación y cierre

- Documentar cada clase Livewire en `docs/modulos/{panel}/{modulo}/{nombre-clase-en-slug}.md`; por ejemplo `amenidades/list-amenidad.md` y `amenidades/save-amenidad.md`.
- Documentar servicios en `docs/servicios/servicio-{nombre-sin-Service-en-slug}.md`; reglas transversales en `logica-{tema}.md`.
- Explicar entradas, acciones, permisos, validaciones, resultados y lógica compleja paso a paso. Actualizar el índice y los enlaces.
- Revisar filtros, estados vacíos, navegación, paginación y permisos afectados. Si hay widgets Alpine, probar entrar, salir y volver mediante navegación Livewire.
- Elegir comprobaciones proporcionales: sintaxis/compilación para plantillas y PHP; pruebas de comportamiento para reglas de negocio; inspección visual para alineación. No añadir tests que solo repitan la implementación ni ejecutar migraciones destructivas por revisar el estilo.
- Aplicar el protocolo de `.agents/AGENTS.md`: objetivo, trabajo, revisión y entrega, informando las verificaciones realmente realizadas.
