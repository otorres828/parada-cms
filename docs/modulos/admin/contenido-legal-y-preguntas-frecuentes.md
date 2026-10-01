# Contenido legal y preguntas frecuentes

## Administración

- Catálogos → Preguntas frecuentes: categorías, búsqueda, creación, edición con TinyMCE, palabras clave, destacados, orden, activación y eliminación.
- Legales → Documentos: expedientes documentales de las empresas.
- Legales → Sobre nosotros, Políticas de privacidad, Políticas de cookies y Términos y condiciones: un formulario por página.
- Empresas → editar: políticas opcionales de embarque y desembarque. Su detalle enlaza a la lectura de políticas con el mismo permiso `empresas/detail`.

Los permisos están en `storage/json/grupo_seccion_permiso_admin.json` y se registran con `GroupSectionPermissionAdminSeeder`. No se conceden automáticamente a administradores restringidos; se asignan desde sus permisos. Root y superadministradores mantienen el acceso habitual.

## Almacenamiento

Las cuatro páginas legales se escriben al guardar en `storage/app/public/json/{pagina}.json`, con título, contenido y fecha de actualización. `ContenidoSitio::leer` permite consumirlas sin consultas a tablas de contenido.

Las categorías se guardan en `categorias_preguntas_frecuentes` y las preguntas o artículos breves en `preguntas_frecuentes`. Cada pregunta pertenece a una categoría y dispone de slug público, resumen, respuesta enriquecida, palabras clave y configuración para artículos populares. El campo `empresas.politicas` contiene las políticas particulares de embarque y desembarque de cada empresa.

## Verificación

```bash
php -d extension=pdo_sqlite -d extension=sqlite3 tests/ContenidoLegalSmoke.php
```

La prueba usa SQLite en memoria y un disco público de prueba. Comprueba la persistencia del contenido, las preguntas frecuentes, las políticas de empresa y sus permisos.
