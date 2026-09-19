<?php

namespace App\Livewire\Admin;

use App\Models\Empresa;
use App\Models\Pasaje;
use App\Models\Programacion;
use App\Models\Reserva;
use App\Services\Admin\Access;
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
        'periodo' => ['except' => 'mes']
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
        $pagadas = $filtros + ['estado_pago' => 'pagado'];
        $resumen = Reserva::searchAdmin('', $pagadas)->selectRaw('COUNT(*) as cantidad, COALESCE(SUM(monto_total), 0) as ventas, COALESCE(SUM(tasa_servicio), 0) as tasas')->first();
        $empresas = Empresa::searchAdmin()->selectRaw('COUNT(*) as total, COALESCE(SUM(CASE WHEN estatus = 1 THEN 1 ELSE 0 END), 0) as activas')->first();
        $salidas = Programacion::searchAdmin('', ['date_from' => $ahora->toDateString(), 'date_to' => $ahora->copy()->addDays(6)->toDateString(), 'activas' => true, 'proximas' => true]);
        $metrics = ['ventas' => $resumen->ventas, 'tasas' => $resumen->tasas, 'reservas_pagadas' => (int) $resumen->cantidad, 'pasajes' => Pasaje::searchAdmin('', $pagadas)->count(), 'pendientes' => Reserva::searchAdmin('', $filtros + ['estado_pago' => 'pendiente'])->count(), 'empresas' => (int) $empresas->total, 'empresas_activas' => (int) $empresas->activas, 'salidas' => (clone $salidas)->count()];
        $ultimasReservas = Reserva::searchAdmin('', $filtros)->with(['usuario', 'programacion.viaje.empresa'])->withCount('pasajes')->orderByDesc('fecha_compra')->orderByDesc('id')->limit(6)->get();
        $proximasSalidas = $salidas->with(['viaje.empresa', 'viaje.origenTerminal', 'viaje.destinoTerminal'])->orderBy('fecha_salida')->orderBy('hora_salida')->orderBy('id')->limit(5)->get();
        $estados = ['pagado' => ['label' => 'Pagadas', 'color' => 'success'], 'pendiente' => ['label' => 'Pendientes', 'color' => 'warning'], 'nuevo' => ['label' => 'Nuevas', 'color' => 'info'], 'fallido' => ['label' => 'Fallidas', 'color' => 'danger'], 'cancelado' => ['label' => 'Canceladas', 'color' => 'secondary'], 'reembolsado' => ['label' => 'Reembolsadas', 'color' => 'primary']];
        $conteos = Reserva::searchAdmin('', $filtros)->selectRaw('estado_pago, COUNT(*) as cantidad')->groupBy('estado_pago')->pluck('cantidad', 'estado_pago');
        $totalReservas = (int) $conteos->sum();
        foreach ($estados as $estado => &$datos) {
            $datos['cantidad'] = (int) ($conteos[$estado] ?? 0);
            $datos['porcentaje'] = $totalReservas ? round($datos['cantidad'] * 100 / $totalReservas) : 0;
        }
        unset($datos);

        return view('livewire.admin.dashboard', ['metrics' => $metrics, 'ultimasReservas' => $ultimasReservas, 'proximasSalidas' => $proximasSalidas, 'estados' => $estados, 'totalReservas' => $totalReservas, 'desde' => $desde, 'hasta' => $ahora]);
    }

    public function boot(): void
    {
        Access::authorize('dashboard', 'list');
    }
}
