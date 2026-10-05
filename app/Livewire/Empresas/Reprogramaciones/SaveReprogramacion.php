<?php

namespace App\Livewire\Empresas\Reprogramaciones;

use App\Livewire\Empresas\EmpresaComponent;
use App\Models\DatoBancario;
use App\Models\PagoReserva;
use App\Models\Pasaje;
use App\Models\ProgramacionTramoPrecio;
use App\Models\Reserva;
use App\Models\TipoCambio;
use App\Services\Empresa\Access;
use App\Services\Empresa\PagoTaquillaService;
use App\Services\Empresa\ReprogramacionService;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Illuminate\Validation\ValidationException;

#[Layout('layouts.crm')]
class SaveReprogramacion extends EmpresaComponent
{
    #[Locked]
    public string $ventaToken = '';

    public string $searchReserva = '';

    public string $reservaId = '';

    public string $fecha = '';

    public string $tarifaId = '';

    public array $pagos = [];

    #[Locked]
    public Collection $cuentas;

    #[Locked]
    public Collection $reservas;

    #[Locked]
    public ?TipoCambio $cambio = null;

    public array $pago = [
        'tipo' => PagoReserva::TIPO_PAGO_MOVIL,
        'moneda' => 'VES',
        'monto' => '',
        'cuenta_id' => '',
        'referencia' => '',
    ];

    public function mount(): void
    {
        $this->ventaToken = 'RP-'.Reserva::generarLocalizador(7);
        $this->fecha = today()->toDateString();
        $this->cuentas = DatoBancario::searchEmpresa($this->usuarioEmpresa->empresa_id);
        $this->cambio = TipoCambio::vigente();
        $this->buscarReservas();
    }

    public function render()
    {
        $original = $this->reservaId !== '' ? ReprogramacionService::original($this->usuarioEmpresa->empresa_id, (int) $this->reservaId) : null;
        
        $opciones = ProgramacionTramoPrecio::paraTaquilla($this->usuarioEmpresa->empresa_id, $this->fecha)
            ->where('origen_terminal_id', $original?->origen_terminal_id ?? 0)
            ->where('destino_terminal_id', $original?->destino_terminal_id ?? 0)
            ->where('programacion_id', '!=', $original?->programacion_id ?? 0)->get();

        $tarifa = $opciones->firstWhere('id', (int) $this->tarifaId);

        $anterior = $original !== null ? (float) bcsub($original->monto_pasajes, $original->descuento_aplicado, 2) : 0;

        $total = $tarifa !== null && $original !== null ? $original->pasajes->sum(function ($pasaje) use ($tarifa) {
            return ($pasaje->numero_asiento !== null ? (float) $tarifa->precio : 0) - (float) $pasaje->descuento;
        }) : 0;

        $diferencia = round($total - $anterior, 2);

        $cambio = $this->cambio;

        $abonado = collect($this->pagos)->sum(function ($pago) use ($cambio) {
            return $pago['moneda'] === 'VES' ? round((float) $pago['monto'] / max(0.01, (float) $cambio?->valor_usd), 2) : (float) $pago['monto'];
        });

        return view('livewire.empresas.reprogramaciones.save-reprogramacion', [
            'original' => $original,
            'opciones' => $opciones,
            'tarifa' => $tarifa,
            'anterior' => $anterior,
            'total' => $total,
            'diferencia' => $diferencia,
            'abonado' => $abonado,
            'cambio' => $cambio,
            'disponibles' => $tarifa !== null ? Pasaje::consultarDisponibilidad($tarifa->id)['disponibles'] : 0,
        ]);
    }

    public function save(): void
    {
        Access::authorize('reprogramaciones', 'add');
        $this->validate([
            'reservaId' => 'required|integer',
            'tarifaId' => 'required|integer',
        ], [
            'required' => 'Selecciona :attribute.',
            'integer' => 'Selecciona una opción válida para :attribute.',
        ], [
            'reservaId' => 'la reserva original',
            'tarifaId' => 'la nueva salida',
        ]);
        ReprogramacionService::registrar($this->usuarioEmpresa, (int) $this->reservaId, (int) $this->tarifaId, $this->pagos, $this->ventaToken);
        session()->flash('empresas_reprogramacion_success', 'Reserva reprogramada correctamente.');
        $this->redirectRoute('empresas.reprogramaciones.list', navigate: true);
    }

    public function updatedReservaId(): void
    {
        $this->reset('tarifaId', 'pagos', 'pago');
        if ($this->reservaId !== '') {
            ReprogramacionService::validarOriginal(ReprogramacionService::original($this->usuarioEmpresa->empresa_id, (int) $this->reservaId));
        }
    }

    public function updatedSearchReserva(): void
    {
        $this->reset('reservaId', 'tarifaId', 'pagos', 'pago');
        $this->buscarReservas();
    }

    public function buscarReservas(): void
    {
        $this->resetValidation('searchReserva');
        $this->reservas = new Collection;
        $search = trim($this->searchReserva);
        if ($search === '') {
            return;
        }

        $query = Reserva::searchAdmin('', ['empresa_id' => $this->usuarioEmpresa->empresa_id])
            ->with('tramoPrecio');
        $exacta = (clone $query)->where(function ($query) use ($search) {
            $query->where('codigo_referencia', strtoupper($search));
            if (ctype_digit($search)) {
                $query->orWhere('id', (int) $search);
            }
        })->first();
        $resultados = $exacta !== null
            ? new Collection([$exacta])
            : Reserva::searchAdmin($search, ['empresa_id' => $this->usuarioEmpresa->empresa_id])
                ->with('tramoPrecio')->orderByDesc('id')->limit(30)->get();

        $mensaje = 'No se encontró una reserva de tu empresa que coincida con la búsqueda.';
        foreach ($resultados as $reserva) {
            try {
                ReprogramacionService::validarOriginal($reserva);
                $this->reservas->push($reserva);
            } catch (ValidationException $exception) {
                $mensaje = collect($exception->errors())->flatten()->first();
            }
        }

        if ($this->reservas->isEmpty()) {
            $this->addError('searchReserva', $mensaje);
            $this->dispatch('empresas_reprogramacion_error', message: $mensaje);
        }
    }

    public function updatedFecha(): void
    {
        $this->reset('tarifaId', 'pagos', 'pago');
    }

    public function updatedTarifaId(): void
    {
        $this->reset('pagos', 'pago');
    }

    public function agregarPago(): void
    {
        Access::authorize('reprogramaciones', 'add');
        Reserva::exigir($this->reservaId !== '' && $this->tarifaId !== '', 'pagos', 'Selecciona la reserva y la nueva salida.');
        $this->pagos[] = PagoTaquillaService::validarPago($this->pago);
        $this->reset('pago');
        $this->resetValidation();
    }

    public function removerPago(int $indice): void
    {
        Access::authorize('reprogramaciones', 'add');
        unset($this->pagos[$indice]);
        $this->pagos = array_values($this->pagos);
    }
}
