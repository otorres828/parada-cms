<?php

namespace App\Livewire\Empresas\Reservas;

use App\Livewire\Empresas\EmpresaComponent;
use App\Models\DatoBancario;
use App\Mail\PasajesTaquilla;
use App\Models\PagoReserva;
use Illuminate\Support\Facades\Mail;
use App\Models\ProgramacionTramoPrecio;
use App\Models\Reserva;
use App\Models\TipoCambio;
use App\Services\Empresa\Access;
use App\Services\Empresa\PagoTaquillaService;
use App\Services\Empresa\ReservaTaquillaService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Illuminate\Support\Str;

#[Layout('layouts.crm')]
class SaveReserva extends EmpresaComponent
{

    #[Locked]
    public string $ventaToken = '';

    public string $fecha = '';

    public string $origenId = '';

    public string $destinoId = '';

    public array $pasajeros = [];

    public array $pagos = [];

    public string $tarifaId = '';

    public Collection $cuentas;

    public array $pago = [
        'tipo' => PagoReserva::TIPO_PAGO_MOVIL,
        'moneda' => 'VES',
        'monto' => '',
        'cuenta_id' => '',
        'referencia' => '',
    ];

    public array $comprador = [
        'nombre' => '', 
        'telefono' => '', 
        'email' => ''
    ];

    public array $pasajero = [
        'nombre' => '',
        'apellido' => '',
        'tipo_documento' => '',
        'documento_identidad' => '',
        'fecha_nacimiento' => '',
        'tipo_pasajero' => 'adulto',
        'con_asiento' => false,
    ];

    public bool $canConfirm = false;

    public bool $canList = false;

    public function mount(): void
    {
        $this->ventaToken = 'TQ-'.Str::ulid();
        $this->fecha = today()->toDateString();
        $this->canConfirm = $this->usuarioEmpresa->hasPermission('reservas', 'confirm');
        $this->canList = $this->usuarioEmpresa->hasPermission('reservas', 'list');
        $this->cuentas =  DatoBancario::searchEmpresa($this->usuarioEmpresa->empresa_id);
    }

    public function render(): View
    {
        $tramos =  ProgramacionTramoPrecio::paraTaquilla($this->usuarioEmpresa->empresa_id, $this->fecha)->get();

        $salidas = $tramos->pluck('programacion')->unique('id')->values();

        $opciones = $tramos->where('origen_terminal_id', (int) $this->origenId)
                            ->where('destino_terminal_id', (int) $this->destinoId);

        $tarifa = $opciones->firstWhere('id', (int) $this->tarifaId);

        $cantidad = collect($this->pasajeros)->filter(function ($pasajero) {
            return $pasajero['tipo_pasajero'] !== 'infante' || ! empty($pasajero['con_asiento']);
        })->count();

        $precio = (float) ($tarifa?->precio ?? 0);
        $cambio = TipoCambio::vigente();

        $abonado = collect($this->pagos)->sum(function ($pago) use ($cambio) {
            return $pago['moneda'] === 'VES' ? round((float) $pago['monto'] / max(0.01, (float) $cambio?->valor_usd), 2) : (float) $pago['monto'];
        });

        return view('livewire.empresas.reservas.save-reserva', [
            'origenes' => $tramos->pluck('origenTerminal')->unique('id')->values(),
            'destinos' => $tramos->where('origen_terminal_id', (int) $this->origenId)->pluck('destinoTerminal')->unique('id')->values(),
            'opciones' => $opciones,
            'precio' => $precio,
            'cantidad' => $cantidad,
            'total' => round($precio * $cantidad, 2),
            'abonado' => $abonado,
            'cambio' => $cambio,
            'salidas' => $salidas,
        ]);
    }

    public function updatedFecha(): void
    {
        $this->reset('tarifaId', 'origenId', 'destinoId');
    }

    public function updatedOrigenId(): void
    {
        $this->reset('destinoId', 'tarifaId');
    }

    public function updatedDestinoId(): void
    {
        $this->reset('tarifaId');
    }

    public function agregarPasajero(): void
    {
        Access::authorize('reservas', 'add');
        Reserva::exigir($this->reservaId === null, 'reserva', 'La venta ya fue registrada.');
        $this->pasajeros[] = ReservaTaquillaService::validarPasajero($this->pasajero);
        $this->reset('pasajero');
        $this->resetValidation();
    }

    public function removerPasajero(int $indice): void
    {
        unset($this->pasajeros[$indice]);
        $this->pasajeros = array_values($this->pasajeros);
    }

    public function agregarPago(): void
    {
        Access::authorize('reservas', 'add');
        $this->pagos[] = PagoTaquillaService::validarPago($this->pago);
        $this->reset('pago');
        $this->resetValidation();
    }

    public function removerPago(int $indice): void
    {
        unset($this->pagos[$indice]);
        $this->pagos = array_values($this->pagos);
    }

    public function registrar(): void
    {
        Access::authorize('reservas', 'add');

        Reserva::exigir($this->reservaId === null, 'reserva', 'La venta ya fue registrada.');

        $this->validate([
            'tarifaId' => 'required|integer',
            'origenId' => 'required|integer',
            'destinoId' => 'required|integer',
        ], [
            'required' => 'Selecciona :attribute.',
            'integer' => 'Selecciona una opción válida.',
        ]);

        ProgramacionTramoPrecio::where('origen_terminal_id', $this->origenId)
                                ->where('destino_terminal_id', $this->destinoId)
                                ->findOrFail($this->tarifaId);

        ReservaTaquillaService::registrar(
            $this->usuarioEmpresa, 
            (int) $this->tarifaId, 
            $this->comprador, 
            $this->pasajeros, 
            $this->pagos, 
            $this->ventaToken
        );

        $this->reset('pasajeros', 'pagos');
        $this->dispatch('successEventList', message: 'Reserva y pagos registrados correctamente.');

    }

    public function confirmarPago(): void
    {
        PagoTaquillaService::confirmar($this->usuarioEmpresa, $this->idReserva());
        $this->dispatch('successEventList', message: 'Pago confirmado. Los pasajes están disponibles.');
    }

    public function rechazarPago(): void
    {
        PagoTaquillaService::rechazar($this->usuarioEmpresa, $this->idReserva());
        $this->dispatch('successEventList', message: 'Pago rechazado.');
    }

    public function cancelar(): void
    {
        ReservaTaquillaService::cancelar($this->usuarioEmpresa, $this->idReserva());
        $this->dispatch('successEventList', message: 'Reserva cancelada.');
    }

    public function enviarPasajes(): void
    {
        Access::authorize('reservas', 'add');
        $reserva = Reserva::taquillaEmpresa($this->usuarioEmpresa->empresa_id)->findOrFail($this->idReserva())->detalle();
        Reserva::exigir($reserva->estado_pago === Reserva::ESTADO_PAGO_PAGADO, 'reserva', 'Solo se pueden enviar pasajes pagados.');
        Reserva::exigir(! empty($reserva->comprador_json['email']), 'comprador', 'El comprador no tiene un correo registrado.');
        Mail::to($reserva->comprador_json['email'])->send(new PasajesTaquilla($reserva));
        $this->dispatch('successEventList', message: 'Pasajes enviados al correo del comprador.');
    }

    private function idReserva(): int
    {
        abort_if($this->reservaId === null, 404);

        return $this->reservaId;
    }
}
