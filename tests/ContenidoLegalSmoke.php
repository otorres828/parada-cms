<?php

// Base SQLite en memoria; el disco público se aísla.
require __DIR__.'/TransportesSmoke.php';

use App\Support\ContenidoSitio;
use Illuminate\Support\Facades\Storage;

$app->instance('env', 'testing');
Storage::fake('public');
Storage::fake('local');
Artisan::call('db:seed', ['--class' => Database\Seeders\GroupSectionPermissionAdminSeeder::class, '--force' => true]);

foreach (ContenidoSitio::PAGINAS as $pagina => $titulo) {
    $editor = new App\Livewire\Admin\Legales\ContenidoPagina;
    $editor->mount($pagina);
    $assert($editor->contenido === '', 'Contenido inicial vacío');
    $editor->contenido = "Primer párrafo.\nSegundo párrafo";
    $editor->save();
    $assert(ContenidoSitio::leer($pagina)['contenido'] === $editor->contenido, 'Persistencia JSON '.$pagina);
    $assert(Storage::disk('public')->exists('json/'.$pagina.'.json'), 'Archivo JSON '.$pagina);
}

$reject(
    fn () => ContenidoSitio::leer('../privado'),
    Symfony\Component\HttpKernel\Exception\HttpException::class,
);

$categoria = App\Models\CategoriaPreguntaFrecuente::create([
    'nombre' => 'Reservas',
    'slug' => 'reservas',
    'descripcion' => 'Ayuda para reservas.',
    'icono' => 'bi-ticket-perforated',
    'destacada' => true,
    'orden' => 1,
    'estatus' => App\Models\CategoriaPreguntaFrecuente::ESTADO_ACTIVE,
]);

$faq = new App\Livewire\Admin\PreguntasFrecuentes\SavePregunta;
$faq->mount();
$faq->categoria_pregunta_frecuente_id = $categoria->id;
$faq->pregunta = '¿Cómo consultar mi reserva?';
$faq->resumen = 'Consulta una reserva desde tu cuenta.';
$faq->respuesta = '<p>Desde tu cuenta.</p>';
$faq->palabras_clave = 'reserva, cuenta';
$faq->orden = 2;
$faq->save();

$registro = App\Models\PreguntaFrecuente::firstOrFail();
$faq->pregunta_id = $registro->id;
$faq->estatus = App\Models\PreguntaFrecuente::ESTADO_INACTIVE;
$faq->save();
$assert($registro->fresh()->estatus === App\Models\PreguntaFrecuente::ESTADO_INACTIVE, 'Desactivar FAQ');

$empresa->update(['politicas' => 'Llegar con antelación.']);
$politicasHtml = (string) Livewire\Livewire::mount(
    App\Livewire\Admin\Empresas\PoliticasEmpresa::class,
    ['empresa_id' => $empresa->id],
);
$assert(str_contains($politicasHtml, 'Llegar con antelación.'), 'Políticas visibles');

$rutaDocumento = 'legales/'.$empresa->id.'/contrato-prueba.pdf';
Storage::disk('local')->put($rutaDocumento, 'contenido de prueba');
$documento = App\Models\DocumentoLegal::create([
    'empresa_id' => $empresa->id,
    'admin_id' => $admin->id,
    'titulo' => 'Contrato de prueba',
    'tipo' => 'contrato',
    'archivo' => $rutaDocumento,
    'nombre_original' => 'contrato-prueba.pdf',
    'mime' => 'application/pdf',
    'tamano' => 19,
]);
$expediente = new App\Livewire\Admin\Legales\EmpresaDocumento;
$expediente->mount($empresa->id);
$expediente->deleteDocumento($documento->id);
$assert(! App\Models\DocumentoLegal::whereKey($documento->id)->exists(), 'Eliminar registro documental');
$assert(! Storage::disk('local')->exists($rutaDocumento), 'Eliminar archivo documental');

foreach ([
    App\Livewire\Admin\PreguntasFrecuentes\ListPregunta::class => [],
    App\Livewire\Admin\PreguntasFrecuentes\SavePregunta::class => ['pregunta_id' => $registro->id],
    App\Livewire\Admin\PreguntasFrecuentes\ListCategoria::class => [],
    App\Livewire\Admin\PreguntasFrecuentes\SaveCategoria::class => ['categoria_id' => $categoria->id],
    App\Livewire\Admin\Legales\ContenidoPagina::class => ['pagina' => 'sobre-nosotros'],
] as $component => $params) {
    $assert(strlen((string) Livewire\Livewire::mount($component, $params)) > 100, 'Render '.$component);
}

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(Illuminate\Http\Request::create(route('admin.legales.sobre-nosotros.edit')));
$assert($response->getStatusCode() === 200, 'Ruta de contenido con página fija: '.$response->getStatusCode());

// El permiso de detalle de empresas basta para consultar sus políticas.
$limitado = App\Models\Admin::create([
    'name' => 'Consulta',
    'username' => 'consulta-contenido',
    'email' => 'consulta@test.test',
    'password' => 'test-password',
    'level' => App\Models\Admin::ADMIN,
    'status' => 1,
]);
$permiso = App\Models\PermissionAdmin::where('url', 'detail')
    ->whereHas('section', function ($query) {
        $query->where('url', 'empresas');
    })
    ->firstOrFail();
$limitado->permissions()->attach($permiso->id, ['status' => 1]);
auth('admin')->setUser($limitado);
$response = $kernel->handle(
    Illuminate\Http\Request::create(route('admin.empresas.politicas', $empresa->id)),
);
$assert($response->getStatusCode() === 200, 'Políticas con permiso de detalle');
$reject(fn () => $editor->save(), Symfony\Component\HttpKernel\Exception\HttpException::class);
$reject(fn () => $faq->save(), Symfony\Component\HttpKernel\Exception\HttpException::class);

auth('admin')->setUser($admin->fresh());
(new App\Livewire\Admin\PreguntasFrecuentes\ListPregunta)->deletePregunta($registro->id);
$assert(
    App\Models\PreguntaFrecuente::find($registro->id)->estatus === App\Models\PreguntaFrecuente::ESTADO_DELETE,
    'Eliminar FAQ',
);

echo "OK: preguntas frecuentes, contenido legal, políticas y permisos.\n";
