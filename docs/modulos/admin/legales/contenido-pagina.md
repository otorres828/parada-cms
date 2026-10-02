# ContenidoPagina

Editor compartido por las cuatro páginas institucionales.

- Clase: [Legales/ContenidoPagina.php](../../../../app/Livewire/Admin/Legales/ContenidoPagina.php).
- Vista: [livewire.admin.legales.contenido-pagina](../../../../resources/views/livewire/admin/legales/contenido-pagina.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermission.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
Access::authorize($this->pagina, 'edit');
~~~

## Funcionamiento paso a paso

1. mount valida la clave de página contra ContenidoSitio::PAGINAS y carga el JSON.
2. render resuelve el título según la página; la vista conecta el editor enriquecido al contenido.
3. save autoriza edit sobre la sección correspondiente a esa página y valida contenido obligatorio hasta 100000 caracteres.
4. ContenidoSitio guarda en disco público y la clase muestra confirmación sin abandonar el formulario.

## Reglas y casos particulares

Cada ruta pasa pagina como valor predeterminado. El atributo Locked evita cambiar esa identidad desde el cliente.

## Validación del formulario

Reglas declaradas en línea. Las condiciones adicionales y las reglas multilínea se consultan en la clase enlazada.

~~~php
'contenido' => 'required|string|max:100000',
~~~

## Métodos de referencia

`mount()`, `render()`, `save()`.

## Componentes de la vista

- `<x-list.heading />`
- `<x-form.cancel-button />`
- `<x-form.rich-text-editor />`
- `<x-layout.error />`
- `<x-layout.loader.fullpage />`

[Volver al índice administrativo](../README.md).
