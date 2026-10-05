<?php

namespace App\Livewire\Empresas\Reservas;

use App\Livewire\Empresas\EmpresaComponent;
use App\Models\DatoBancario;
use App\Models\PagoReserva;
use App\Models\Pasaje;
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
        $this->ventaToken = 'TQ-'.Reserva::generarLocalizador(7);
        $this->fecha = today()->toDateString();
        $this->canConfirm = $this->usuarioEmpresa->hasPermission('reservas', 'confirm');
        $this->canList = $this->usuarioEmpresa->hasPermission('reservas', 'list');
        $this->cuentas =  DatoBancario::searchEmpresa($this->usuarioEmpresa->empresa_id);
    }

    public function render(): View
    {
        $tramos = ProgramacionTramoPrecio::opcionesTaquilla(
            $this->usuarioEmpresa->empresa_id,
            $this->fecha,
            (int) $this->origenId,
            (int) $this->destinoId,
            (int) $this->tarifaId
        );
        $tarifa = $tramos['tarifa'];

        $cantidad = collect($this->pasajeros)->filter(function ($pasajero) {
            return $pasajero['tipo_pasajero'] !== 'infante' || ! empty($pasajero['con_asiento']);
        })->count();

        $precio = (float) ($tarifa?->precio ?? 0);
        $disponibles = $tarifa !== null ? Pasaje::consultarDisponibilidad($tarifa->id)['disponibles'] : 0;
        $cambio = TipoCambio::vigente();

        $abonado = collect($this->pagos)->sum(function ($pago) use ($cambio) {
            return $pago['moneda'] === 'VES' ? round((float) $pago['monto'] / max(0.01, (float) $cambio?->valor_usd), 2) : (float) $pago['monto'];
        });

        return view('livewire.empresas.reservas.save-reserva', array_merge($tramos, [
            'precio' => $precio,
            'cantidad' => $cantidad,
            'disponibles' => $disponibles,
            'total' => round($precio * $cantidad, 2),
            'abonado' => $abonado,
            'cambio' => $cambio,
            'puedeAgregarPasajeros' => $tarifa !== null,
            'puedeAgregarPagos' => $tarifa !== null && $cantidad > 0,
        ]));
    }

    public function registrar(): void
    {
        Access::authorize('reservas', 'add');

        $this->validate([
            'tarifaId' => 'required|integer',
            'origenId' => 'required|integer',
            'destinoId' => 'required|integer',
        ], [
            'required' => 'Selecciona :attribute.',
            'integer' => 'Selecciona una opción válida.',
        ], [
            'tarifaId' => 'la salida',
            'origenId' => 'el origen',
            'destinoId' => 'el destino',
        ]);

        $this->validarTramo();

        ReservaTaquillaService::registrar(
            $this->usuarioEmpresa,
            (int) $this->tarifaId,
            $this->comprador,
            $this->pasajeros,
            $this->pagos,
            $this->ventaToken
        );

        $this->reset('pasajeros', 'pagos', 'pasajero', 'pago', 'comprador', 'tarifaId');
        $this->ventaToken = 'TQ-'.Reserva::generarLocalizador(7);
        $this->resetValidation();
        $this->dispatch('empresas_reserva_success', message: 'Reserva y pagos registrados correctamente.');

    }

    public function updatedFecha(): void
    {
        $this->reset('tarifaId', 'origenId', 'destinoId', 'pagos', 'pago');
    }

    public function updatedOrigenId(): void
    {
        $this->reset('destinoId', 'tarifaId', 'pagos', 'pago');
    }

    public function updatedDestinoId(): void
    {
        $this->reset('tarifaId', 'pagos', 'pago');
    }

    public function updatedTarifaId(): void
    {
        $this->reset('pagos', 'pago');
    }

    public function agregarPasajero(): void
    {
        Access::authorize('reservas', 'add');
        $this->validarTramo();
        $this->pasajeros[] = ReservaTaquillaService::validarPasajero($this->pasajero);
        $this->reset('pasajero');
        $this->resetValidation();
        $this->dispatch('cotizacion-actualizada', secciones: ['pasajeros', 'resumen']);
    }

    public function removerPasajero(int $indice): void
    {
        Access::authorize('reservas', 'add');
        unset($this->pasajeros[$indice]);
        $this->pasajeros = array_values($this->pasajeros);
        if ($this->pasajeros === []) {
            $this->reset('pagos', 'pago');
        }
        $this->dispatch('cotizacion-actualizada', secciones: $this->pasajeros === []
            ? ['pasajeros', 'pagos', 'resumen']
            : ['pasajeros', 'resumen']);
    }

    public function agregarPago(): void
    {
        Access::authorize('reservas', 'add');
        $this->validarTramo();
        Reserva::exigir(count($this->pasajeros) > 0, 'pasajeros', 'Agrega los pasajeros antes de registrar un pago.');
        if ((int) $this->pago['tipo'] !== PagoReserva::TIPO_PAGO_MOVIL) {
            Access::authorize('reservas', 'confirm');
        }
        $this->pagos[] = PagoTaquillaService::validarPago($this->pago);
        $this->reset('pago');
        $this->resetValidation();
        $this->dispatch('cotizacion-actualizada', secciones: ['pagos', 'resumen']);
    }

    public function removerPago(int $indice): void
    {
        Access::authorize('reservas', 'add');
        unset($this->pagos[$indice]);
        $this->pagos = array_values($this->pagos);
        $this->dispatch('cotizacion-actualizada', secciones: ['pagos', 'resumen']);
    }

    private function validarTramo(): ProgramacionTramoPrecio
    {
        $tarifa = ProgramacionTramoPrecio::searchTramos($this->usuarioEmpresa->empresa_id, $this->fecha, [
            'origen_terminal_id' => $this->origenId,
            'destino_terminal_id' => $this->destinoId,
        ])->find($this->tarifaId);

        Reserva::exigir($tarifa !== null, 'tarifaId', 'Selecciona una salida disponible antes de continuar.');

        return $tarifa;
    }
}
