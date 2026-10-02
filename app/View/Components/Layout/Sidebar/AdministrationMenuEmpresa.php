<?php

namespace App\View\Components\Layout\Sidebar;

use Illuminate\View\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\UsuarioEmpresa;
use Closure;
use Illuminate\Contracts\View\View;

class AdministrationMenuEmpresa extends Component
{
    public ?UsuarioEmpresa $usuario;

    public bool $listDashboard = false;

    public bool $listUsuarios = false;

    public bool $editPoliticasEmbarque = false;

    public bool $listDatosBancarios = false;

    public bool $listViajes = false;

    public bool $listProgramaciones = false;

    public bool $listTransportes = false;

    public bool $listReservas = false;

    public bool $listPasajes = false;

    public bool $listValidacionPagos = false;

    public bool $listReembolsos = false;

    public bool $listReprogramaciones = false;

    public bool $listOrdenesCobro = false;

    public bool $listCupones = false;

    public bool $listReporteVentas = false;

    public bool $listReporteRutas = false;

    public bool $profileAccount = false;

    public bool $passwordAccount = false;

    public function __construct()
    {
        $this->usuario = Auth::guard('empresa')->user();

        if (! $this->usuario || $this->usuario->estatus !== UsuarioEmpresa::ESTADO_ACTIVE) {
            return;
        }

        $this->profileAccount = true;
        $this->passwordAccount = true;

        $permissions = $this->usuario->checkPermissionsBatch([
            'listDashboard' => ['dashboard', 'list'],
            'listUsuarios' => ['usuarios', 'list'],
            'editPoliticasEmbarque' => ['politicas-embarque', 'edit'],
            'listDatosBancarios' => ['datos-bancarios', 'list'],
            'listViajes' => ['viajes', 'list'],
            'listProgramaciones' => ['programaciones', 'list'],
            'listTransportes' => ['transportes', 'list'],
            'listReservas' => ['reservas', 'list'],
            'listPasajes' => ['pasajes', 'list'],
            'listValidacionPagos' => ['validacion-pagos', 'list'],
            'listReembolsos' => ['reembolsos', 'list'],
            'listReprogramaciones' => ['reprogramaciones', 'list'],
            'listOrdenesCobro' => ['ordenes-cobro', 'list'],
            'listCupones' => ['cupones', 'list'],
            'listReporteVentas' => ['reporte-ventas', 'list'],
            'listReporteRutas' => ['reporte-rutas', 'list'],
        ]);

        foreach ($permissions as $property => $hasPermission) {
            $this->{$property} = $hasPermission;
        }
    }

    public function render(): View|Closure|string
    {
        return view('components.layout.sidebar.administration-menu-empresa');
    }
}
