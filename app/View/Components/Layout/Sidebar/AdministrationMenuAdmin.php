<?php

namespace App\View\Components\Layout\Sidebar;

use App\Models\Admin;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AdministrationMenuAdmin extends Component
{
    public ?Admin $admin;

    public bool $listDashboard = false;

    public bool $listAdmins = false;

    public bool $listEmpresas = false;

    public bool $listUsers = false;

    public bool $listViajes = false;

    public bool $listProgramaciones = false;

    public bool $listTransportes = false;

    public bool $listReservas = false;

    public bool $listPasajes = false;

    public bool $listReembolsos = false;

    public bool $listOrdenesCobro = false;

    public bool $listcupones = false;

    public bool $listTerminales = false;

    public bool $listAmenidades = false;

    public bool $salesReportes = false;

    public bool $companiesReportes = false;

    public bool $exchangeRatesReport = false;

    public bool $listLegales = false;

    public bool $listTasasServicio = false;

    public bool $listExoneracionesTasaServicio = false;

    public bool $listAuditoria = false;

    public bool $listSolicitudes = false;

    public bool $listPreguntas = false;

    public bool $editSobreNosotros = false;

    public bool $editPrivacidad = false;

    public bool $editCookies = false;

    public bool $editTerminos = false;

    public bool $profileAccount = false;

    public bool $passwordAccount = false;

    public function __construct()
    {
        $this->admin = auth('admin')->user();

        if (! $this->admin || $this->admin->status !== Admin::ACTIVO) {
            return;
        }

        $this->profileAccount = true;
        $this->passwordAccount = true;

        $permissions = $this->admin->checkPermissionsBatch([
            'listDashboard' => ['dashboard', 'list'],
            'listAdmins' => ['admins', 'list'],
            'listEmpresas' => ['empresas', 'list'],
            'listUsers' => ['clientes', 'list'],
            'listViajes' => ['viajes', 'list'],
            'listProgramaciones' => ['programaciones', 'list'],
            'listTransportes' => ['transportes', 'list'],
            'listReservas' => ['reservas', 'list'],
            'listPasajes' => ['pasajes', 'list'],
            'listReembolsos' => ['reembolsos', 'list'],
            'listOrdenesCobro' => ['ordenes-cobro', 'list'],
            'listcupones' => ['cupones', 'list'],
            'listTerminales' => ['terminales', 'list'],
            'listAmenidades' => ['amenidades', 'list'],
            'listPreguntas' => ['preguntas-frecuentes', 'list'],
            'salesReportes' => ['reportes', 'list-sales'],
            'companiesReportes' => ['reportes', 'list-companies'],
            'exchangeRatesReport' => ['reportes', 'list-exchange-rates'],
            'listLegales' => ['legales', 'list'],
            'editSobreNosotros' => ['sobre-nosotros', 'edit'],
            'editPrivacidad' => ['politicas-privacidad', 'edit'],
            'editCookies' => ['politicas-cookies', 'edit'],
            'editTerminos' => ['terminos-condiciones', 'edit'],
            'listTasasServicio' => ['tasas-servicio', 'list'],
            'listExoneracionesTasaServicio' => ['exoneraciones-tasa-servicio', 'list'],
            'listAuditoria' => ['auditoria', 'list'],
            'listSolicitudes' => ['solicitudes', 'list'],
        ]);

        foreach ($permissions as $property => $hasPermission) {
            $this->{$property} = $hasPermission;
        }
    }

    public function render(): View|Closure|string
    {
        return view('components.layout.sidebar.administration-menu-admin');
    }
}
