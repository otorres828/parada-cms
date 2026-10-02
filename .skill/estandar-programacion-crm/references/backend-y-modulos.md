# Backend, módulos y permisos

## Organización de clases

Usar nombres por responsabilidad: `ListAmenidad`, `SaveAmenidad`, `DetailReserva`. Un mismo `Save*` atiende alta y edición mediante un ID nullable si ambas operaciones existen. Mantener nombres especiales descriptivos cuando la pantalla no encaja en esas tres funciones.

Orden habitual de la clase:

1. Namespace e imports necesarios.
2. Atributos Livewire, como `#[Layout('layouts.cms')]` para Admin.
3. Traits utilizados.
4. Propiedades tipadas, IDs protegidos y configuración de query string.
5. `mount`, `render`, hooks de filtros y acciones públicas.
6. Métodos auxiliares de carga, edición y validación.

Los IDs que fijan el registro o contexto de una pantalla usan `#[Locked]`. Eso no sustituye comprobar pertenencia y autorización. Tipar una selección vacía como `string` si el formulario usa `''`; no declarar `int` y asignarle después una cadena vacía. Colecciones y modelos se tipan con su clase real. No redefinir propiedades del framework con tipos incompatibles.

## Listados

Referencias: `app/Livewire/Admin/Amenidades/ListAmenidad.php` y `Reservas/ListReserva.php`.

- Reutilizar `Listing`, `Permissions` y `WithPagination` según las funciones de la pantalla.
- `mount` inicializa orden, filtros predeterminados, catálogos y permisos visuales.
- Persistir búsqueda, filtros y cantidad por página en `protected array $queryString`, con valores `except` coherentes. `Listing` ya aporta propiedades de orden con `#[Url]`.
- `render` obtiene un Builder del modelo, aplica orden, pagina y entrega los datos a la vista.
- Reiniciar la página cuando cambia un filtro, `search` o `per_page`.
- No materializar todo con `get()` para después paginar en PHP.

```php
public function render()
{
    $query = Amenidad::searchAdmin($this->search, [
        'status' => $this->status,
    ]);

    $query = $this->applySort($query);

    return view('livewire.admin.amenidades.list-amenidad', [
        'amenidades' => $query->paginate($this->per_page),
    ]);
}

public function updated(string $property): void
{
    if (in_array($property, ['search', 'status', 'per_page'], true)) {
        $this->resetPage();
    }
}
```

No sobrescribir filtros recibidos por URL sin motivo. Para rangos de fechas reutilizar `TraitGeneral::getDefaultDesde()` y `getDefaultHasta()` como valores iniciales: última semana hasta hoy. Aplicar también la normalización y límites de `TraitGeneral::date()` en la consulta; los límites HTML no protegen parámetros enviados manualmente. No crear filtros de fechas en módulos donde no aportan valor, como los usuarios de una empresa.

## Consultas en modelos

`searchAdmin(string $search = '', array $filters = []): Builder` construye la consulta; no decide paginación ni dibuja HTML. Las consultas largas o reutilizables de detalles también pertenecen al modelo apropiado.

```php
$query->where(function ($query) use ($search) {
    $query->where('nombre', 'like', '%'.$search.'%');
    $query->orWhere('codigo', 'like', '%'.$search.'%');
});

$status = $filters['status'] ?? null;

if ($status !== null && $status !== '') {
    $query->where('estatus', $status);
} else {
    $query->where('estatus', '!=', self::ESTADO_DELETE);
}
```

No usar `empty($status)` para comprobar un filtro que admite `0`. Castear estados como enteros cuando existen más de dos valores; representar su etiqueta con el método o componente del modelo.

Usar `whereHas` con los filtros de la relación necesarios; no reutilizar indiscriminadamente otro `searchAdmin` si incorpora condiciones o cargas ajenas a esta consulta. Cargar las relaciones consumidas por la vista para evitar N+1.

Si una relación ya está cargada porque se necesita su contenido, contar esa colección con `->count()`. Si solo se necesita una cantidad, evaluar `withCount` en esa consulta concreta. No incorporarlo globalmente a consultas que luego se usan para agregaciones ni cargar miles de registros solo para contarlos.

## Formularios y detalles

En `Save*`, `mount` carga el registro y los catálogos. Un método `editar(Modelo $modelo)` asigna las propiedades del formulario; `validateForm(): array` reúne reglas cuando ayuda a mantener legible `save`.

```php
$data = $this->validate([
    'nombre' => ['required', 'string', 'max:255'],
    'estatus' => ['required', 'integer', 'in:1,2'],
]);
```

Una regla compleja ocupa varias líneas; nunca comprimir varios atributos en una sola. Conservar etiquetas de error en español. La validación del navegador complementa la del servidor.

El guardado autoriza la operación, valida y persiste. Reutilizar `DB::transaction` cuando varias escrituras deban ser atómicas y `Audit::record` para las acciones administrativas auditadas. No repetir la misma autorización varias veces dentro de una sola acción sin necesidad.

El guardado normal puede usar `session()->flash('admin_success', ...)` y redirigir al listado con `navigate: true`, como los módulos existentes. Si se agrega un registro dentro de un detalle, como documentos de empresa, permanecer en el detalle, limpiar el formulario, actualizar el listado y emitir la alerta. No imponer una redirección universal.

En `Detail*`, cargar la entidad, sus relaciones y permisos en la clase. Paginar historiales crecientes: programaciones de transporte/ruta y reservas de cliente. Para varias tablas paginadas independientes, usar nombres de paginador distintos y resetear el correspondiente. No añadir paginación artificial a una pequeña colección fija.

## Permisos y rutas

Fuentes: `routes/admin.php`, `app/Http/Middleware/CheckPermissionAdmin.php`, `app/Traits/Permissions.php`, `app/Services/Admin/Access.php` y `resources/views/components/layout/sidebar/administration-menu-admin.blade.php`.

- Mantener el mismo orden por grupo y módulo en menú, imports de Livewire, rutas y mapa del middleware. Leer el orden vigente, no reconstruirlo de memoria.
- Agrupar rutas con `prefix` y `name`; registrar pantallas con `Route::livewire`. Los IDs numéricos llevan `whereNumber`.
- Registrar los permisos de la sección en la fuente JSON usada por `GroupSectionPermissionAdminSeeder`. Localizar el archivo leyendo el seeder; no suponer una carpeta.
- Toda pantalla protegida debe tener su autorización de entrada o una excepción explícita. Perfil y contraseña requieren autenticación, pero no permisos de módulo.
- En `mount`, usar `checkPermissions('modulo', ['detail'])` o `Access::allows` para enlaces específicos. Pasar esos booleanos a componentes.
- En `save`, cambio de estado, eliminación, aprobación y exportación, autorizar en backend la acción correspondiente. Ocultar el botón no impide invocar una acción Livewire.
- No llamar `Access`, `Auth::user()->hasPermission` ni consultas de permisos desde las vistas.
- No crear permisos innecesarios: por ejemplo, políticas de una empresa heredan el permiso para ver su detalle.

En Empresas, el contexto de empresa debe derivarse de la sesión autorizada y restringir consultas, IDs de acciones y exportaciones. Adaptar permisos y guard al panel real; `Permissions` y `Admin\Access` actuales son específicos de Admin. No asumir que un prefijo de URL aísla datos.

## Servicios, migraciones y exportaciones

- La clase Livewire coordina interfaz, validación y llamada al dominio. Las consultas y relaciones van en el modelo pertinente; los procesos de negocio en servicios con responsabilidad definida.
- Ejemplos: cupones en `CuponService`, tasas en `TasasServicioService`, transiciones de pago en `PagoReservaService` y viajeros en `ViajeroService`. Consultar sus documentos antes de cambiar reglas.
- Mantener las operaciones de agregar y remover pasajeros explícitas. No duplicar la creación del viajero si el flujo ya recibe un viajero existente.
- Evitar helpers innecesarios y servicios enormes. Compartir reglas realmente comunes mediante la abstracción existente adecuada.
- Bloquear filas solo cuando exista una carrera concreta que pueda violar una regla. No quitar un bloqueo que protege disponibilidad o transiciones solo porque las solicitudes suelen ser poco frecuentes.
- Mientras siga vigente la etapa de desarrollo acordada por el usuario, modificar la migración original en lugar de crear migraciones `add_*`. Esto no autoriza ejecutar `migrate:fresh`; reconsiderar el enfoque cuando haya datos productivos que preservar.
- Reutilizar exportaciones en `app/Exports` y `Excel::download(..., '*.xlsx')`, con filtros y orden equivalentes al listado. No implementar otro CSV manual para la misma función.
- Usar consultas y chunks como los exports existentes, columnas omitidas por parámetro cuando corresponda y tipos seguros para texto de usuario. Los chunks no garantizan que una descarga enorme termine antes del timeout HTTP.
- El permiso de descarga debe coincidir entre botón y servidor. No copiar excepciones antiguas como si fueran el patrón universal.

## Ejemplos que no deben convertirse en reglas

El código heredado tiene propiedades sin tipar, casts booleanos de estados, autorizaciones duplicadas y formularios con `in:0,1`. Seguir el estándar del usuario para código nuevo. Los estados de pagos o programaciones tienen sus propias constantes: no aplicarles automáticamente la semántica de un catálogo.

La eliminación física es una regla específica de documentos y otras entidades que así lo requieran; no generalizarla. En documentos se elimina registro y archivo, considerando que una transacción SQL no revierte el filesystem.
