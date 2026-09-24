<?php

namespace App\Livewire\Admin;

use App\Models\Empresa;
use App\Models\Pasaje;
use App\Models\Programacion;
use App\Models\Reserva;
use App\Services\Admin\Access;
use App\Services\ReservaService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.cms')]
#[Title('Resumen de la plataforma')]
class Dashboard extends Component
{
    public string $periodo = 'mes';

    protected array $queryString = [
        'periodo' => ['except' => 'mes'],
    ];

    public function render()
    {
        $ahora = Carbon::now();
        $periodo = in_array($this->periodo, ['hoy', '7', '30', 'mes'], true) ? $this->periodo : 'mes';
        $desde = match ($periodo) {
            'hoy' => $ahora->copy()->startOfDay(),
            '7' => $ahora->copy()->subDays(6)->startOfDay(),
            '30' => $ahora->copy()->subDays(29)->startOfDay(),
            default => $ahora->copy()->startOfMonth(),
        };
        $filtros = ['date_from' => $desde->toDateString(), 'date_to' => $ahora->toDateString()];
        $pagadas = $filtros + ['estado_pago' => Reserva::ESTADO_PAGO_PAGADO];
        $resumen = Reserva::dashboardSummary($pagadas);
        $empresas = Empresa::dashboardSummary();
        $salidas = Programacion::upcomingForDashboard(
            $ahora->toDateString(),
            $ahora->copy()->addDays(6)->toDateString(),
        );
        $metrics = [
            'ventas' => $resumen->ventas,
            'tasas' => $resumen->tasas,
            'reservas_pagadas' => (int) $resumen->cantidad,
            'pasajes' => Pasaje::searchAdmin('', $pagadas)->count(),
            'pendientes' => Reserva::searchAdmin('', $filtros + ['estado_pago' => Reserva::ESTADO_PAGO_PENDIENTE])->count(),
            'empresas' => (int) $empresas->total,
            'empresas_activas' => (int) $empresas->activas,
            'salidas' => (clone $salidas)->count(),
        ];
        $ultimasReservas = Reserva::latestForDashboard($filtros);
        $proximasSalidas = (clone $salidas)
            ->limit(5)
            ->get();
        $estados = [
            Reserva::ESTADO_PAGO_PAGADO => ['label' => 'Pagadas', 'color' => 'success'],
            Reserva::ESTADO_PAGO_PENDIENTE => ['label' => 'Pendientes', 'color' => 'warning'],
            Reserva::ESTADO_PAGO_NUEVO => ['label' => 'Nuevas', 'color' => 'info'],
            Reserva::ESTADO_PAGO_FALLIDO => ['label' => 'Fallidas', 'color' => 'danger'],
            Reserva::ESTADO_PAGO_CANCELADO => ['label' => 'Canceladas', 'color' => 'secondary'],
            Reserva::ESTADO_PAGO_REEMBOLSADO => ['label' => 'Reembolsadas', 'color' => 'primary'],
        ];
        $conteos = Reserva::statusCountsForDashboard($filtros);
        $totalReservas = (int) $conteos->sum();
        foreach ($estados as $estado => &$datos) {
            $datos['cantidad'] = (int) ($conteos[$estado] ?? 0);
            $datos['porcentaje'] = $totalReservas ? round(($datos['cantidad'] * 100) / $totalReservas) : 0;
        }
        unset($datos);

        return view('livewire.admin.dashboard', [
            'metrics' => $metrics,
            'ultimasReservas' => $ultimasReservas,
            'proximasSalidas' => $proximasSalidas,
            'estados' => $estados,
            'totalReservas' => $totalReservas,
            'desde' => $desde,
            'hasta' => $ahora,
            'disponibilidadTramos' => ReservaService::consultarDisponibilidadPorTramos($proximasSalidas),
        ]);
    }

    public function boot(): void
    {
        Access::authorize('dashboard', 'list');
    }
}
