# Alpine, formularios y widgets

## Organización del script

Usar Alpine para interacción de la pantalla, validación del navegador y widgets. Livewire mantiene la persistencia y las reglas del servidor. Registrar objetos con nombre reconocible: `listAmenidad`, `saveAmenidad`, `detailViaje`.

```blade
<div x-data="saveAmenidad" class="py-3">
    {{-- Contenido del formulario --}}
</div>

@script
    <script>
        Alpine.data('saveAmenidad', () => ({
            saving: false,
            validator: null,
            toastCleanup: [],

            init() {
                // Inicialización específica del formulario.
            },

            destroy() {
                this.toastCleanup.forEach(cleanup => cleanup());
                this.validator?.destroy();
            },
        }));
    </script>
@endscript
```

Los comentarios del ejemplo indican puntos de adaptación, no código terminado para copiar. No acumular lógica extensa en un atributo `x-data` ni registrar eventos globales en cada navegación sin limpieza.

## Formulario y validación

Patrón de referencia: `resources/views/livewire/admin/amenidades/save-amenidad.blade.php`.

- Mostrar `<x-layout.error />` y mensajes `@error` junto a cada campo.
- Formulario con ID propio, `x-ref="form"`, `@submit.prevent="preSave"` y `novalidate` cuando JustValidate se encarga de la experiencia de validación.
- Reutilizar `x-form.container-sm/md`, `text-input`, `dropdown`, `textarea` y componentes apropiados.
- Vincular campos sencillos mediante `x-model="$wire.nombre"` o el binding Livewire existente adecuado al momento de sincronización requerido. No añadir `.live` indiscriminadamente a cada pulsación.
- Inicializar `JustValidate` después de `$nextTick`, buscando campos dentro de `this.$refs.form` para no mezclar formularios.
- Mantener clases `invalid-feedback`, `is-invalid`, `is-valid` y mensajes en español.
- Deshabilitar Guardar con `saving` y `wire:loading.attr="disabled"` para evitar envíos repetidos.

```javascript
async preSave() {
    if (this.saving || !this.validator || !await this.validator.revalidate()) {
        return;
    }

    this.saving = true;

    try {
        await $wire.call('save');
    } finally {
        this.saving = false;
    }
}
```

El servidor sigue validando todos los campos relevantes aunque el formulario use Alpine. Adaptar reglas condicionales de ambos lados sin duplicar cálculos de negocio en JavaScript.

## Alertas y ciclo de vida

Reutilizar el store toast y eventos existentes `successEventList` y `errorEventList`. Conservar las funciones que retorna `Livewire.on` y ejecutarlas en `destroy`.

```javascript
this.toastCleanup = [
    Livewire.on('successEventList', data => this.$store.toast.success(data.message)),
    Livewire.on('errorEventList', data => this.$store.toast.info(data.message)),
];
```

Si el guardado redirige, consumir una vez `session()->pull('admin_success')` mediante `@js(...)` y emitir la alerta al inicializar el listado, como los módulos existentes. Para Empresas verificar su convención de mensajes en lugar de mezclar sin revisión estado de sesión de Admin.

Liberar validadores, listeners, observadores y editores al destruir el componente. La navegación `wire:navigate` debe funcionar tanto en la primera carga como al ir a otra pantalla y regresar.

## Búsqueda

- Búsqueda de registros de base de datos: `wire:model.live.debounce.1200ms="search"`, `searchAdmin` y reinicio de paginación.
- Filtros select: `wire:model.live` y valores compatibles con la propiedad.
- Búsqueda local de una colección ya cargada o snapshot: `x-model.debounce` y filtrado Alpine; no simular que busca registros fuera de la colección disponible.
- La búsqueda local de reservas de una orden no cambia por sí sola las filas de una descarga del servidor. Mantener esa diferencia explícita o conectar ambos filtros si el requerimiento pide exportar solo lo visible.
- Buscadores remotos de widgets usan debounce y cancelación de la solicitud anterior cuando corresponde.

## Componentes con estado compartido

Para widgets reutilizables, pasar los nombres de propiedades como props y sincronizarlos con `$wire.entangle`, correctamente escrito. Ejemplo real del mapa:

```blade
@props([
    'address' => 'direccion',
    'latitude' => 'latitud',
    'longitude' => 'longitud',
])

<div x-data="locationMap({
    address: $wire.entangle(@js($address)),
    latitude: $wire.entangle(@js($latitude)),
    longitude: $wire.entangle(@js($longitude)),
})">
    {{-- Contenedor del widget --}}
</div>
```

Usar `@js` para transportar valores PHP a JavaScript. No concatenar contenido del usuario dentro de código JS o HTML de un modal sin escapar. Evitar sincronizaciones circulares entre watchers y callbacks de la librería.

## Mapa y editor enriquecido

Reutilizar `resources/views/components/form/location-map.blade.php` y `rich-text-editor.blade.php` en lugar de implementar otro widget en cada vista.

- `wire:ignore` se aplica al DOM administrado por la librería, no a toda la pantalla.
- Mapa dentro de un contenedor con altura definida, ancho máximo, posición y overflow controlados. Cargar CSS de Leaflet antes de inicializar; ajustar tamaño cuando cambia el contenedor. Limitar búsquedas a Venezuela según la función existente.
- Editor TinyMCE con altura adaptable a la pantalla según el componente. No convertirlo en textarea simple en formularios de contenido legal.
- Carga de librerías reutilizable: no duplicar scripts ni dar por hecha su carga al navegar.
- Inicializar después de disponer del DOM; comprobar si el componente fue destruido durante una carga asíncrona.
- Al salir, eliminar la instancia, observadores/listeners y cancelar solicitudes pendientes. Evitar reutilizar una instancia asociada a un elemento ya eliminado.
- Si hay varias instancias, usar IDs únicos y referencias locales.

Verificar al menos el recorrido que falló anteriormente: abrir una página legal, navegar a otra desde el menú y volver. El primer render correcto no demuestra que el widget soporte navegación Livewire.
