# Documentación funcional de Parada CMS

Esta carpeta explica el comportamiento vigente del proyecto. No contiene planes futuros, bitácoras de cambios ni diagramas históricos.

## Organización

- [`modulos/admin`](modulos/admin/README.md): módulos disponibles para los administradores de la plataforma.
- [`modulos/empresas`](modulos/empresas/README.md): espacio reservado para documentar el panel operativo de las empresas.
- [`servicios`](servicios/README.md): reglas de negocio, entidades involucradas y servicios que coordinan los procesos.

## Criterio de mantenimiento

En `servicios`, los documentos de clases usan `servicio-` seguido del nombre de la clase en minúsculas y separado por guiones, quitando únicamente el sufijo `Service`: `ReservaService` → `servicio-reserva.md`, `TasasServicioService` → `servicio-tasas-servicio.md`. Las clases `Admin\Access` y `Admin\Audit` tienen documentos separados.

Las reglas y flujos que abarcan varias entidades usan `logica-`, por ejemplo `logica-checkout.md`. Los `README.md` son índices y quedan fuera de esta convención. En `modulos/admin`, cada clase Livewire tiene un documento dentro de la carpeta de su módulo, con el nombre de la clase convertido a slug: `Amenidades/ListAmenidad.php` → `amenidades/list-amenidad.md` y `Amenidades/SaveAmenidad.php` → `amenidades/save-amenidad.md`. Los documentos generales complementan estas explicaciones individuales.

Cada documento debe describir el comportamiento actual, sus estados, permisos, reglas y relaciones relevantes. Cuando una implementación cambie, debe actualizarse el documento correspondiente en el mismo cambio.
