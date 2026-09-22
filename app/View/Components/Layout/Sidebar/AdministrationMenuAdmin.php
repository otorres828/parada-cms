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

public bool $listSections = false;

public bool $listViajes = false;

    public bool $listProgramaciones = false;

    public bool $listAutobuses = false;

    public bool $listReservas = false;

    public bool $listPasajes = false;



    public bool $listReembolsos = false;

    public bool $listCampanas = false;

public bool $listTerminales = false;

    public bool $listAmenidades = false;

    public bool $salesReportes = false;

    public bool $companiesReportes = false;


    public bool $generalSettings = false;

    public bool $listLegales = false;
    public bool $listTasasServicio = false;
    public bool $listAuditoria = false;

    public bool $profileAccount = false;

    public bool $passwordAccount = false;

    public function __construct()
    {
        $this->admin = auth('admin')->user();

        if (! $this->admin || $this->admin->status !== Admin::ACTIVO) {
            return;
        }

        $permissions = $this->admin->checkPermissionsBatch([
            'listDashboard' => ['dashboard', 'list'],
            'listAdmins' => ['admins', 'list'],
            'listEmpresas' => ['empresas', 'list'],
            'listUsers' => ['users', 'list'],
            'listViajes' => ['viajes', 'list'],
            'listProgramaciones' => ['programaciones', 'list'],
            'listAutobuses' => ['autobuses', 'list'],
            'listReservas' => ['reservas', 'list'],
            'listPasajes' => ['pasajes', 'list'],
            'listReembolsos' => ['reembolsos', 'list'],
            'listCampanas' => ['campanas', 'list'],
            'listTerminales' => ['terminales', 'list'],
            'listAmenidades' => ['amenidades', 'list'],
            'salesReportes' => ['reportes', 'sales'],
            'companiesReportes' => ['reportes', 'companies'],
            'generalSettings' => ['settings', 'general'],
            'listLegales' => ['legales', 'list'],
            'listTasasServicio' => ['tasas-servicio', 'list'],
            'listAuditoria' => ['auditoria', 'list'],
            'profileAccount' => ['account', 'profile'],
            'passwordAccount' => ['account', 'password'],
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
