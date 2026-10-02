<?php

namespace App\Livewire\Empresas;

use App\Models\Pasaje;
use App\Models\Empresa;
use App\Models\Programacion;
use App\Models\Reserva;
use App\Models\ViajeTramo;
use App\Traits\TraitGeneral;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.crm')]
#[Title('Resumen de la empresa')]
class Dashboard extends EmpresaComponent
{
    use TraitGeneral;

    public string $periodo = 'mes';

    public string $date_from = '';

    public string $date_to = '';

    public bool $canAddReserva = false;

    public bool $canListReservas = false;

    public bool $canListProgramaciones = false;

    public array $estados =  [
            Reserva::ESTADO_PAGO_PAGADO => ['label' => 'Pagadas', 'color' => 'success'],
            Reserva::ESTADO_PAGO_PENDIENTE => ['label' => 'Pendientes', 'color' => 'warning'],
            Reserva::ESTADO_PAGO_NUEVO => ['label' => 'Nuevas', 'color' => 'info'],
            Reserva::ESTADO_PAGO_FALLIDO => ['label' => 'Fallidas', 'color' => 'danger'],
            Reserva::ESTADO_PAGO_CANCELADO => ['label' => 'Canceladas', 'color' => 'secondary'],
            Reserva::ESTADO_PAGO_REPROGRAMADO => ['label' => 'Reprogramadas', 'color' => 'primary'],
            Reserva::ESTADO_PAGO_REEMBOLSADO => ['label' => 'Reembolsadas', 'color' => 'primary'],
    ];

    protected array $queryString = [
        'periodo' => ['except' => 'mes'],
        'date_from' => ['except' => ''],
        'date_to' => ['except' => ''],
    ];

    public function mount(): void
    {
        $permisos = $this->usuarioEmpresa->checkPermissionsBatch([
            'reservas' => ['reservas', 'list'],
            'programaciones' => ['programaciones', 'list'],
        ]);
        $this->canAddReserva = $this->usuarioEmpresa->hasPermission('reservas', 'add');
        $this->canListReservas = $permisos['reservas'];
        $this->canListProgramaciones = $permisos['programaciones'];

        if ($this->date_from === '' || $this->date_to === '') {
            $this->applyPeriod();
        }
    }

    public function render()
    {
        $ahora = Carbon::now();
        $desde = $this->parseDate($this->date_from, $ahora->copy()->startOfMonth(), 'date_from');
        $hasta = $this->parseDate($this->date_to, $ahora, 'date_to')->endOfDay();

        if ($desde->greaterThan($hasta)) {
            $desde = $hasta->copy()->startOfDay();
        }

        $empresa = $this->usuarioEmpresa->empresa;
        $ellosReciben = (int) $empresa->tipo_contrato === Empresa::CONTRATO_ELLOS_RECIBEN;

        $filtros = [
            'empresa_id' => $empresa->id,
            'tipo_transporte' => $empresa->getTipoTransporte(),
            'date_from' => $desde->toDateString(),
            'date_to' => $hasta->toDateString(),
        ];
        $pagadas = $filtros + ['estado_pago' => Reserva::ESTADO_PAGO_PAGADO];
        $resumen = Reserva::dashboardSummary($pagadas);
        $salidas = Programacion::upcomingForDashboard(
            $ahora->toDateString(),
            $ahora->copy()->addDays(6)->toDateString(),
            $empresa->id,
            $empresa->getTipoTransporte(),
        );

        $metrics = [
            'ventas' => $ellosReciben ? $resumen->ventas : (float) $resumen->ventas - (float) $resumen->tasas,
            'tasas' => $resumen->tasas,
            'reservas_pagadas' => (int) $resumen->cantidad,
            'pasajes' => Pasaje::searchAdmin('', $pagadas)->count(),
            'pendientes' => Reserva::searchAdmin('', $filtros + ['estado_pago' => Reserva::ESTADO_PAGO_PENDIENTE])->count(),
            'salidas' => (clone $salidas)->count(),
        ];

        $ultimasReservas = Reserva::latestForDashboard($filtros);
        $proximasSalidas = (clone $salidas)
            ->limit(5)
            ->get();
        
        $conteos = Reserva::statusCountsForDashboard($filtros);
        $totalReservas = (int) $conteos->sum();

        foreach ($this->estados as $estado => &$datos) {
            $datos['cantidad'] = (int) ($conteos[$estado] ?? 0);
            $datos['porcentaje'] = $totalReservas ? round(($datos['cantidad'] * 100) / $totalReservas) : 0;
        }

        unset($datos);

        return view('livewire.empresas.dashboard', [
            'metrics' => $metrics,
            'ellosReciben' => $ellosReciben,
            'ultimasReservas' => $ultimasReservas,
            'proximasSalidas' => $proximasSalidas,
            'estados' => $this->estados,
            'totalReservas' => $totalReservas,
            'desde' => $desde,
            'hasta' => $hasta,
            'disponibilidadTramos' => ViajeTramo::disponibilidadPorTramos($proximasSalidas),
        ]);
    }

    public function updatedPeriodo(): void
    {
        if ($this->periodo !== 'personalizado') {
            $this->applyPeriod();
        }
    }

    public function updatedDateFrom(): void
    {
        $this->periodo = 'personalizado';
    }

    public function updatedDateTo(): void
    {
        $this->periodo = 'personalizado';
    }

    private function applyPeriod(): void
    {
        $ahora = Carbon::now();
        $periodo = in_array($this->periodo, ['hoy', '7', '30', 'mes'], true) ? $this->periodo : 'mes';

        $desde = match ($periodo) {
            'hoy' => $ahora->copy()->startOfDay(),
            '7' => $ahora->copy()->subDays(6)->startOfDay(),
            '30' => $ahora->copy()->subDays(29)->startOfDay(),
            default => $ahora->copy()->startOfMonth(),
        };

        $this->date_from = $desde->toDateString();
        $this->date_to = $ahora->toDateString();
    }

    private function parseDate(string $date, Carbon $fallback, string $attribute): Carbon
    {
        try {
            return Carbon::createFromFormat('Y-m-d', self::date($date, $attribute))->startOfDay();
        } catch (\Throwable) {
            $this->addError($attribute, 'La fecha indicada no es válida o está fuera del rango permitido.');

            return $fallback;
        }
    }
}
