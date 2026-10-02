# EmpresaDocumento

Carga, consulta y eliminación física de documentos de una empresa.

- Clase: [Legales/EmpresaDocumento.php](../../../../app/Livewire/Admin/Legales/EmpresaDocumento.php).
- Vista: [livewire.admin.legales.empresa-documento](../../../../resources/views/livewire/admin/legales/empresa-documento.blade.php).
- Registro de rutas: [admin.php](../../../../routes/admin.php).

## Acceso y permisos

La entrada está protegida por autenticación administrativa y el permiso de su ruta en [CheckPermission](../../../../app/Http/Middleware/CheckPermissionAdmin.php). Los permisos visuales y las autorizaciones de acciones declaradas en esta clase se enumeran a continuación.

Comprobaciones declaradas por la clase. Cuando hay una condición entre alta y edición, el permiso depende de la operación:

~~~php
$this->checkPermissions('legales');
Access::allows('legales', 'file');
Access::authorize('legales', 'add');
Access::authorize('legales', 'delete');
~~~

## Funcionamiento paso a paso

1. mount valida empresa y obtiene permisos legales y file; render filtra documentos por empresa, texto y tipo.
2. save autoriza add y exige título, tipo válido y PDF/JPG/PNG/WEBP de hasta 10 MB; almacena en el disco local.
3. Crea el registro y auditoría en transacción; si falla la base de datos elimina el archivo recién subido.
4. Al guardar limpia formulario, validación y página, emite legalSaved y alerta y permanece en la pantalla.
5. deleteDocumento autoriza delete, busca dentro de la empresa y bloquea el registro; elimina archivo y fila, audita y refresca paginación.

## Reglas y casos particulares

La eliminación es definitiva. La transacción SQL no puede restaurar un archivo borrado si una operación SQL posterior falla. Ver o descargar usa el permiso legales/file y el controlador de documentos.

## Filtros y estado en URL

- `search`
- `tipo_filtro`
- `per_page`

## Validación del formulario

Reglas declaradas en línea. Las condiciones adicionales y las reglas multilínea se consultan en la clase enlazada.

~~~php
'titulo' => 'required|string|max:255',
'tipo' => ['required', Rule::in(array_keys(DocumentoLegal::TIPOS))],
'observaciones' => 'nullable|string|max:4000',
'archivo' => 'required|file|mimes:pdf,jpg,jpeg,png,webp|mimetypes:application/pdf,image/jpeg,image/png,image/webp|max:10240',
~~~

## Métodos de referencia

`mount()`, `render()`, `save()`, `deleteDocumento()`, `updated()`.

Listing aporta búsqueda, selección y ordenación. applySort valida que la columna exista y usa la clave primaria como respaldo. WithPagination gestiona la página; el flujo anterior indica cuándo se reinicia.

## Componentes de la vista

- `<x-list.heading />`
- `<x-form.cancel-button />`
- `<x-form.text-input />`
- `<x-form.dropdown />`
- `<x-list.button-group />`
- `<x-list.view-button />`
- `<x-list.delete-button />`
- `<x-layout.loader.fullpage />`
- `<x-empresas.description />`

[Volver al índice administrativo](../README.md).
