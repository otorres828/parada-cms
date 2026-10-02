<?php

use App\Http\Middleware\CheckPermissionEmpresa;
use App\Models\GroupEmpresa;
use App\Models\PermissionEmpresa;
use App\Models\UsuarioEmpresa;
use App\Services\Empresa\Access;
use Database\Seeders\GroupSectionPermissionEmpresaSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['cache.default' => 'array', 'session.driver' => 'array']);

(new PermisosEmpresaSmoke)->run();
class PermisosEmpresaSmoke
{
    protected function setUp(): void
    {

        // Base exclusiva en memoria: nunca modifica los datos locales del CRM.
        config(['database.connections.permisos_test' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]]);
        DB::setDefaultConnection('permisos_test');

        foreach ([
            '0002_01_01_00001_create_groups_empresa_table.php',
            '0002_01_01_00002_create_sections_empresa_table.php',
            '0002_01_01_00003_create_permissions_empresa_table.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }

        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
        });
        (require database_path('migrations/2026_09_14_000002_create_usuarios_empresa_table.php'))->up();
        (require database_path('migrations/2026_09_14_000003_create_permisos_usuarios_empresa_table.php'))->up();
        DB::table('empresas')->insert([['id' => 1], ['id' => 2]]);
        $this->seed(GroupSectionPermissionEmpresaSeeder::class);
    }

    protected function tearDown(): void
    {
        DB::disconnect('permisos_test');
        Auth::forgetGuards();
    }

    public function test_catalogo_se_actualiza_sin_duplicar_ni_perder_asignaciones(): void
    {
        $usuario = $this->usuario();
        $permiso = $this->permiso('reservas', 'list');
        $usuario->permisos()->attach($permiso);
        $cantidad = PermissionEmpresa::count();

        GroupEmpresa::whereKey(2)->update(['name' => 'Nombre anterior', 'url' => 'anterior']);
        $this->seed(GroupSectionPermissionEmpresaSeeder::class);

        $this->assertSame(7, GroupEmpresa::count());
        $this->assertSame(15, DB::table('sections_empresa')->count());
        $this->assertSame($cantidad, PermissionEmpresa::count());
        $this->assertSame('administracion', GroupEmpresa::findOrFail(2)->url);
        $this->assertTrue($usuario->hasPermission('reservas', 'list'));
        $this->assertSame($permiso->id, $usuario->permissions()->firstOrFail()->id);
    }

    public function test_admin_booleano_tiene_acceso_y_usuario_inactivo_no(): void
    {
        $usuario = $this->usuario(true);

        $this->assertTrue($usuario->isAdmin());
        $this->assertTrue($usuario->hasPermission('usuarios', 'permissions'));

        $usuario->estatus = UsuarioEmpresa::ESTADO_INACTIVE;
        $this->assertFalse($usuario->hasPermission('usuarios', 'permissions'));
        $this->assertFalse($usuario->hasPermission('account', 'edit'));
    }

    public function test_permiso_es_por_usuario_y_requiere_toda_la_jerarquia_activa(): void
    {
        $usuario = $this->usuario();
        $otro = $this->usuario(false, 2);
        $permiso = $this->permiso('reservas', 'list');
        $usuario->permisos()->attach($permiso);

        $this->assertTrue($usuario->hasPermission('reservas', 'list'));
        $this->assertFalse($otro->hasPermission('reservas', 'list'));
        $this->assertFalse($usuario->hasPermission('reservas', 'download'));
        $this->assertTrue($usuario->hasPermission('account', 'edit'));

        foreach ([$permiso, $permiso->section, $permiso->section->group] as $modelo) {
            $modelo->update(['status' => 0]);
            $this->assertFalse($usuario->hasPermission('reservas', 'list'));
            $modelo->update(['status' => 1]);
        }

        Auth::guard('empresa')->setUser($usuario);
        $this->assertTrue(Access::allows('reservas', 'list'));
        $usuario->permisos()->detach($permiso);
        $this->assertFalse(Access::allows('reservas', 'list'));
    }

    public function test_middleware_empresa_redirige_invitado_y_respeta_dashboard_y_cuenta(): void
    {
        $middleware = new CheckPermissionEmpresa;
        $next = function () {
            return response('permitido');
        };

        $response = $middleware->handle($this->solicitud('empresas.dashboard'), $next);
        $this->assertSame(route('empresas.login'), $response->getTargetUrl());

        $usuario = $this->usuario();
        Auth::guard('empresa')->setUser($usuario);
        $response = $middleware->handle($this->solicitud('empresas.dashboard'), $next);
        $this->assertSame(route('empresas.account.profile'), $response->getTargetUrl());
        $this->assertSame(200, $middleware->handle($this->solicitud('empresas.account.profile'), $next)->getStatusCode());

        $usuario->permisos()->attach($this->permiso('dashboard', 'list'));
        $this->assertSame(200, $middleware->handle($this->solicitud('empresas.dashboard'), $next)->getStatusCode());
    }

    public function test_middleware_no_permite_rutas_protegidas_sin_mapeo(): void
    {
        Auth::guard('empresa')->setUser($this->usuario(true));
        try {
            (new CheckPermissionEmpresa)->handle($this->solicitud('empresas.sin-permiso'), function () {
                return response('no debe llegar');
            });
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
            return;
        }

        throw new RuntimeException('Una ruta sin mapeo debe rechazarse.');
    }

    public function run(): void
    {
        foreach (get_class_methods($this) as $method) {
            if (! str_starts_with($method, 'test_')) {
                continue;
            }

            $this->setUp();
            try {
                $this->{$method}();
                echo "OK: {$method}\n";
            } finally {
                $this->tearDown();
                DB::purge('permisos_test');
            }
        }
    }

    private function seed(string $seeder): void
    {
        app($seeder)->run();
    }

    private function assertSame(mixed $expected, mixed $actual): void
    {
        if ($expected !== $actual) {
            throw new RuntimeException('Esperado '.var_export($expected, true).'; recibido '.var_export($actual, true));
        }
    }

    private function assertTrue(bool $actual): void
    {
        $this->assertSame(true, $actual);
    }

    private function assertFalse(bool $actual): void
    {
        $this->assertSame(false, $actual);
    }
    private function usuario(bool $esAdmin = false, int $empresaId = 1): UsuarioEmpresa
    {
        return UsuarioEmpresa::create([
            'empresa_id' => $empresaId,
            'nombre' => 'Usuario de prueba',
            'email' => uniqid('usuario-', true).'@example.test',
            'password' => 'password-prueba',
            'es_admin' => $esAdmin,
            'estatus' => UsuarioEmpresa::ESTADO_ACTIVE,
        ]);
    }

    private function permiso(string $section, string $action): PermissionEmpresa
    {
        return PermissionEmpresa::where('url', $action)
            ->whereHas('section', function ($query) use ($section) {
                $query->where('url', $section);
            })
            ->firstOrFail();
    }

    private function solicitud(string $name): Request
    {
        $request = Request::create('/empresa/dashboard');
        $route = (new Route('GET', 'empresa/dashboard', function () {}))->name($name);
        $request->setRouteResolver(function () use ($route) {
            return $route;
        });

        return $request;
    }
}
