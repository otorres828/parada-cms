<?php

// Base SQLite en memoria; los correos y el disco público se aíslan.
require __DIR__.'/TransportesSmoke.php';

use App\Models\TicketAyuda;
use App\Notifications\ActualizacionTicket;
use App\Services\TicketAyudaService;
use App\Support\ContenidoSitio;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

$app->instance('env', 'testing');
config(['app.customer_ticket_url' => 'https://pasajes.test/centro-ayuda/tickets/{ticket}']);
Notification::fake();
Storage::fake('public');
Artisan::call('db:seed', ['--class' => Database\Seeders\GroupSectionPermissionAdminSeeder::class, '--force' => true]);

foreach (ContenidoSitio::PAGINAS as $pagina => $titulo) {
    $editor = new App\Livewire\Admin\Legales\ContenidoPagina;
    $editor->mount($pagina);
    $assert($editor->contenido === '', 'Contenido inicial vacío');
    $editor->contenido = "Primer párrafo.\nSegundo párrafo <script>alert(1)</script>";
    $editor->save();
    $assert(ContenidoSitio::leer($pagina)['contenido'] === $editor->contenido, 'Persistencia JSON '.$pagina);
    $assert(Storage::disk('public')->exists('json/'.$pagina.'.json'), 'Archivo JSON '.$pagina);
}
$reject(fn () => ContenidoSitio::leer('../privado'), Symfony\Component\HttpKernel\Exception\HttpException::class);

$faq = new App\Livewire\Admin\PreguntasFrecuentes\SavePregunta;
$faq->mount();
$faq->pregunta = '¿Cómo consultar mi reserva?';
$faq->respuesta = 'Desde tu cuenta.';
$faq->orden = 2;
$faq->save();
$registro = App\Models\PreguntaFrecuente::firstOrFail();
$faq->pregunta_id = $registro->id;
$faq->estatus = App\Models\PreguntaFrecuente::ESTADO_INACTIVE;
$faq->save();
$assert($registro->fresh()->estatus === App\Models\PreguntaFrecuente::ESTADO_INACTIVE, 'Desactivar FAQ');

$empresa->update(['politicas' => "Llegar con antelación.\n<script>alert(1)</script>"]);
$politicasHtml = (string) Livewire\Livewire::mount(App\Livewire\Admin\Empresas\PoliticasEmpresa::class, ['empresa_id' => $empresa->id]);
$assert(str_contains($politicasHtml, 'Llegar con antelación.'), 'Políticas visibles');
$assert(! str_contains($politicasHtml, '<script>alert(1)</script>'), 'Escapar políticas');

$ticket = TicketAyuda::create([
    'usuario_id' => $cliente->id,
    'nombre' => $cliente->getNameLastName(),
    'email' => $cliente->email,
    'asunto' => 'Necesito ayuda con mi pasaje',
    'categoria' => 'reservas',
    'estado' => 'abierto',
]);
$ticket->mensajes()->create([
    'autor' => 'cliente',
    'mensaje' => 'Consulta inicial',
]);
$assert($ticket->email === $cliente->email && $ticket->mensajes()->count() === 1, 'Ticket creado por el sitio público');
$detalle = new App\Livewire\Admin\CentroAyuda\DetailTicket;
$detalle->mount($ticket->id);
$detalle->mensaje = 'Primera respuesta <script>alert(1)</script>';
$detalle->responder();
$assert($ticket->fresh()->estado === 'esperando_cliente', 'Estado después de respuesta');
$assert($ticket->mensajes()->reorder('id', 'desc')->first()->admin_id === $admin->id, 'Autor de respuesta');

$notificacion = Notification::sent(new AnonymousNotifiable, ActualizacionTicket::class)->last();
$correo = $notificacion->toMail(new AnonymousNotifiable);
$assert($correo->viewData['enlace'] === 'https://pasajes.test/centro-ayuda/tickets/'.$ticket->id, 'Enlace hacia el proyecto público');
$assert(! str_contains(view($correo->view, $correo->viewData)->render(), '<script>alert(1)</script>'), 'Escapar correo');
$assert($notificacion->afterCommit === true, 'Notificar después de commit');

$detalle->estado = 'resuelto';
$detalle->guardarEstado();
$detalle->estado = 'cerrado';
$detalle->guardarEstado();
$reject(fn () => TicketAyudaService::responder($ticket->id, 'No debe guardarse', $admin), Illuminate\Validation\ValidationException::class);
$detalle->estado = 'en_atencion';
$detalle->guardarEstado();

foreach ([
    App\Livewire\Admin\PreguntasFrecuentes\ListPregunta::class => [],
    App\Livewire\Admin\PreguntasFrecuentes\SavePregunta::class => ['pregunta_id' => $registro->id],
    App\Livewire\Admin\CentroAyuda\ListTicket::class => [],
    App\Livewire\Admin\CentroAyuda\DetailTicket::class => ['ticket_id' => $ticket->id],
    App\Livewire\Admin\Legales\ContenidoPagina::class => ['pagina' => 'sobre-nosotros'],
] as $component => $params) {
    $assert(strlen((string) Livewire\Livewire::mount($component, $params)) > 100, 'Render '.$component);
}

// El CRM únicamente conserva las rutas administrativas.
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$assert(! Route::has('ayuda.index') && ! Route::has('ayuda.ticket'), 'Sin rutas públicas de centro de ayuda');
$response = $kernel->handle(Illuminate\Http\Request::create(route('admin.sobre-nosotros.edit')));
$assert($response->getStatusCode() === 200, 'Ruta de contenido con página fija: '.$response->getStatusCode());

// El permiso de detalle de empresas basta para sus políticas, pero no permite editar contenidos ni tickets.
$limitado = App\Models\Admin::create(['name' => 'Consulta', 'username' => 'consulta-soporte', 'email' => 'consulta@test.test', 'password' => 'test-password', 'level' => App\Models\Admin::ADMIN, 'status' => 1]);
$permiso = App\Models\PermissionAdmin::where('url', 'detail')->whereHas('section', fn ($q) => $q->where('url', 'empresas'))->firstOrFail();
$limitado->permissions()->attach($permiso->id, ['status' => 1]);
auth('admin')->setUser($limitado);
$response = $kernel->handle(Illuminate\Http\Request::create(route('admin.empresas.politicas', $empresa->id)));
$assert($response->getStatusCode() === 200, 'Políticas con permiso de detalle');
$reject(fn () => $detalle->responder(), Symfony\Component\HttpKernel\Exception\HttpException::class);
$reject(fn () => $editor->save(), Symfony\Component\HttpKernel\Exception\HttpException::class);
$reject(fn () => $faq->save(), Symfony\Component\HttpKernel\Exception\HttpException::class);
auth('admin')->setUser($admin->fresh());
(new App\Livewire\Admin\PreguntasFrecuentes\ListPregunta)->deletePregunta($registro->id);
$assert(App\Models\PreguntaFrecuente::find($registro->id)->estatus === App\Models\PreguntaFrecuente::ESTADO_DELETE, 'Eliminar FAQ');
echo "OK: FAQ, JSON, políticas, permisos, seguimiento administrativo y notificaciones de tickets.\n";
