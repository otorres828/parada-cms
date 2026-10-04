# Vistas, componentes y presentación

## Espaciado e indentación

Aplicar `.editorconfig`: cuatro espacios, sin tabs, UTF-8 y LF. En Blade dejar una línea vacía entre bloques funcionales: cabecera, slots, filtros, tabla, paginación y loader. Indentar hijos respecto del contenedor y directivas respecto de su ámbito.

No compactar un formulario, `<tr>` completo o pares `<dt>/<dd>` en una sola línea. Tampoco intercalar varias líneas vacías entre instrucciones sencillas. Dividir atributos largos de manera consistente; no forzar un atributo por línea para botones cortos.

PHP usa llaves en línea seRodando para clases/métodos y `function (...) { ... }` para closures. Separar propiedades y métodos con una línea vacía. Arrays de validación, datos y configuración: una pareja clave/valor por línea. Los JSON de permisos usan estructura jerárquica legible; los metadatos breves del grupo/sección y cada permiso pueden ocupar una línea, según el formato existente.

## Comentario principal

Toda vista principal de listado, formulario o detalle comienza con comentario Blade en español: título, descripción real, componentes directos y función de cada uno. Actualizarlo al extraer o sustituir componentes. No copiar nombres inexistentes de otra pantalla.

```blade
{{--
    AMENIDADES — LISTADO
    --------------------------------------------------------------------------
    Permite buscar y consultar las amenidades, filtrar por estado y ordenar los
    resultados. Las acciones disponibles dependen de los permisos del usuario.

    Componentes reutilizables utilizados:
    - <x-list.heading />: Título de la pantalla.
    - <x-list.actions />: Buscador, filtros y acción de registro.
    - <x-list.search-input />: Búsqueda reactiva.
    - <x-list.table />: Contenedor de la tabla.
    - <x-layout.loader.fullpage />: Indicador durante las operaciones.
    --------------------------------------------------------------------------
--}}
```

El ejemplo es ilustrativo: completar la lista con los componentes efectivamente usados. Los componentes pequeños pueden llevar un comentario breve propio. Comentar la intención de bloques complejos, no cada etiqueta obvia. No usar HTML `<!-- -->` para explicaciones internas que no deben ir al navegador.

## Estructura de una pantalla

1. Comentario principal.
2. `@section('title', ...)`.
3. Un elemento raíz, habitualmente `div.py-3`, con `x-data` si necesita Alpine.
4. `<x-list.heading>` con título; formularios/detalles incluyen regreso en el slot `button`.
5. Filtros, formulario o contenido.
6. Paginación cuando corresponda y `<x-layout.loader.fullpage wire:loading.delay.short />`.
7. `@script` para registrar el comportamiento Alpine local cuando exista.

No añadir un objeto Alpine vacío por obligación a una pantalla que no lo necesita. Conservar navegación interna con `wire:navigate` y URLs generadas con `route()`.

## Listados y alineación

Reutilizar `x-list.actions` y sus slots `search`, `group` y `button`.

```blade
<x-list.actions>

    <x-slot:search>
        <x-list.search-input wire:model.live.debounce.1200ms="search" />
    </x-slot:search>

    <x-slot:group>
        <select class="form-select" style="width: 240px; max-width: 100%;"
            wire:model.live="status" aria-label="Filtrar por estado">
            <option value="">Todos los estados</option>
            <option value="1">Activo</option>
            <option value="2">Inactivo</option>
        </select>
    </x-slot:group>

    @if ($canAdd)
        <x-slot:button>
            <x-list.add-button :route="route('admin.amenidades.add')">
                Nuevo registro
            </x-list.add-button>
        </x-slot:button>
    @endif

</x-list.actions>
```

Adaptar la ruta al módulo; no mostrar Nuevo si la operación no existe, aunque haya un booleano genérico. El botón no va en una fila superior seRodando del buscador. Si solo hay búsqueda y un select, usar el slot `group` en lugar de una fila debajo que ocupe toda la pantalla.

Con varios filtros, usar `row g-3 mb-3 align-items-end` y columnas como `col-md-6 col-xl-2`. El botón Excel va al extremo derecho con `ms-xl-auto`, alineado con los controles. En móvil permitir apilado sin overflow.

Para una cabecera de tarjeta con título y herramientas, el contenedor flex debe ocupar `w-100`; agrupar búsqueda y botón en un bloque con `ms-lg-auto`. Referencia: cabecera «Reservas incluidas» de `livewire/admin/ordenes-cobro/detail-orden-cobro.blade.php`. No centrar las herramientas en la mitad de la tarjeta.

## Tablas y paginación

- Reutilizar `x-list.table`, `x-list.sortable-button`, `x-list.button-group` y botones de acciones existentes.
- Cada fila Livewire tiene `wire:key` estable y específico del módulo/registro.
- Usar `@forelse` y una fila vacía con `colspan` correcto.
- Acciones a la derecha con `text-end`; mantener consistencia en números, fechas y estados.
- Mostrar enlaces solo con permiso. Sin permiso, mostrar el dato como texto si el usuario puede consultar esa información.
- No hacer consultas Eloquent dentro de bucles Blade ni recalcular reglas de negocio en el HTML.
- En listados de servidor, usar `WithPagination`, paginador Eloquent y `{{ $registros->links() }}`; respetar el tema y vista de paginación configurados por el proyecto.
- No sustituir esa paginación por el componente Alpine `pagination` solo porque existe. Este último sirve a flujos específicos con datos del navegador.
- Paginar también historiales crecientes dentro de detalles, manteniendo búsqueda/filtros cuando corresponda.

## Descomposición de detalles

La vista principal organiza la pantalla. Las tablas grandes y descripciones se extraen en la carpeta del módulo, incluso si inicialmente tienen un solo consumidor.

```text
resources/views/components/viajes/
    description.blade.php
    programaciones-table.blade.php
    tramo-precios-table.blade.php
```

```blade
<x-viajes.programaciones-table
    :programaciones="$programaciones"
    :can-view-passengers="$canViewPassengers"
/>
```

El componente declara `@props(['programaciones', 'canViewPassengers'])`. Nombres de atributos Blade en kebab-case y variables PHP en camelCase. Los datos y permisos se pasan desde el padre; no se resuelven mediante consultas ocultas en el componente.

En componentes de descripción, usar `card-body`, `dl.row`, `dt` y `dd` alineados. Separar identidad/ruta, cliente, cupón y montos con `<hr>` cuando mejore la lectura. No poner un enlace al mismo detalle en el identificador de esa pantalla; sí enlazar una entidad relacionada cuando haya permiso.

Mantener los estados vacíos y valores alternativos (`—` o «No registrado») coherentes. Cupón aplicado solo aparece si existe; QR solo cuando el pasaje está habilitado por el estado pagado. Reutilizar `x-list.pasaje-qr` y su modal SweetAlert en vez de crear modales distintos por pantalla.

Para importes reutilizar el componente monetario vigente. No inferir reglas financieras del orden de las etiquetas: el subtotal, descuento, tasa y total deben venir del modelo/servicio que corresponda. Origen y destino de reservas/pasajes son los contratados, no los extremos generales de la programación.

## Fuentes visuales de referencia

- Listado sencillo: `resources/views/livewire/admin/amenidades/list-amenidad.blade.php` (corregir conceptualmente su opción histórica `0` inactivo al seguir el estándar).
- Filtros por columnas y Excel: `resources/views/livewire/admin/reservas/list-reserva.blade.php`.
- Detalle dividido: `resources/views/livewire/admin/viajes/detail-viaje.blade.php`.
- Descripción por bloques: `resources/views/components/reservas/description.blade.php`.
- Slots y alineación: `resources/views/components/list/actions.blade.php`.

Son ejemplos de estructura, no autorización para copiar sus inconsistencias o modificar sus reglas.

## Convención explícita para vistas Save

Dejar una línea vacía entre componentes y bloques. Escribir cada parámetro de un componente en su propia línea y el cierre en otra línea, alineado con su apertura. En las vistas Save, el botón Volver al listado se muestra sin condicionar por `canList`.

## Separación de divs en todas las vistas

Este formato aplica a todas las vistas y componentes Blade, tanto de Admin como de Empresas:

- Dejar una línea vacía después de la apertura de cada `div` y antes de su cierre.
- Separar con una línea vacía los bloques `div` hermanos, especialmente las columnas de una fila.
- Indentar cada nivel con cuatro espacios y alinear los cierres con sus aperturas.
- Mantener una línea vacía entre componentes y bloques de contenido.
- Escribir cada parámetro de un componente en una línea independiente, cuatro espacios dentro de la apertura; colocar `/>` o `>` en otra línea alineada con la apertura.
- No compactar columnas, contenedores ni componentes con parámetros en una sola línea. No dejar espacios al final de las líneas.

```blade
<div class="container-fluid px-0 mb-4">

    <div class="row g-3">

        <div class="col-md-5">

            <x-programacion.description
                :programacion="$programacion"
                :capacidad="$capacidad"
                :show-empresa="false"
                route-viaje="empresas.viajes.detail"
                route-transporte="empresas.transportes.detail"
            />

        </div>

        <div class="col-md-7">

            <x-programacion.rates-matrix
                :programacion="$programacion"
                :disponibilidad-tramos="$disponibilidadTramos"
                :tipo-cambio="$tipoCambioVigente"
            />

        </div>

    </div>

</div>
```
