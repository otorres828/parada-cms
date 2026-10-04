<?php

namespace App\Livewire\Empresas\Programaciones;

use App\Livewire\Empresas\EmpresaComponent;
use App\Models\Programacion;
use App\Models\Transporte;
use App\Models\Viaje;
use App\Services\Empresa\Access;
use App\Services\Empresa\ProgramacionService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;

#[Layout('layouts.crm')]
class SaveProgramacion extends EmpresaComponent
{
    #[Locked]
    public ?int $programacionId = null;

    public bool $canList = false;

    public string $viajeId = '';

    public string $transporteId = '';

    public string $fechaSalida = '';

    public string $horaSalida = '06:00';

    public int $estatus = 1;

    public array $tramos = [];

    public function mount(?int $programacion_id = null): void
    {
        $this->canList = $this->usuarioEmpresa->hasPermission('programaciones', 'list');
        $this->fechaSalida = today()->format('Y-m-d');

        if ($programacion_id !== null) {
            $programacion = $this->findProgramacion($programacion_id);
            Programacion::exigir(in_array($programacion->estatus, [1, 2], true), 'estatus', 'Una programación finalizada no se puede editar.');
            Programacion::exigir(! $programacion->reservas()->exists(), 'tramos', 'Esta programación tiene reservas y no puede editarse.');
            $this->programacionId = $programacion->id;
            $this->viajeId = (string) $programacion->viaje_id;
            $this->transporteId = (string) $programacion->transporte_id;
            $this->estatus = $programacion->estatus;
            $this->fechaSalida = $programacion->getSalida()?->format('Y-m-d') ?? $this->fechaSalida;
            $this->horaSalida = $programacion->getSalida()?->format('H:i') ?? '06:00';
            $this->cargarTramos();

            foreach ($this->tramos as &$tramo) {
                $tramo['habilitado'] = false;
            }
            unset($tramo);
            foreach ($programacion->tramoPrecios as $tramo) {
                $clave = $tramo->origen_terminal_id.'-'.$tramo->destino_terminal_id;
                $this->tramos[$clave] = [
                    'habilitado' => true,
                    'precio' => $tramo->precio,
                    'fecha_salida' => $tramo->fecha_salida?->format('Y-m-d') ?? '',
                    'hora_salida' => substr($tramo->hora_salida ?? '', 0, 5),
                    'fecha_llegada' => $tramo->fecha_llegada?->format('Y-m-d') ?? '',
                    'hora_llegada' => substr($tramo->hora_llegada ?? '', 0, 5),
                ];
            }
        }
    }

    public function findProgramacion(int $id): Programacion
    {
        return Programacion::searchAdmin('', [
            'empresa_id' => $this->usuarioEmpresa->empresa_id,
        ])->findOrFail($id);
    }

    public function updatedViajeId(): void
    {
        $this->tramos = [];
        $this->cargarTramos();
    }

    public function cargarTramos(): void
    {
        if ($this->viajeId === '') {
            return;
        }
        $this->validate([
            'fechaSalida' => ['required', 'date_format:Y-m-d', 'after_or_equal:today', 'before_or_equal:2100-12-31'],
            'horaSalida' => ['required', 'date_format:H:i'],
        ], [
            'required' => 'Indica la fecha y la hora inicial.',
            'date_format' => 'La fecha o la hora tienen un formato inválido.',
            'after_or_equal' => 'La salida no puede ser anterior a hoy.',
            'before_or_equal' => 'La fecha supera el año permitido.',
        ]);
        $viaje = Viaje::searchAdmin('', [
            'empresa_id' => $this->usuarioEmpresa->empresa_id,
            'status' => Viaje::ESTADO_ACTIVE,
        ])->with('tramos')->findOrFail($this->viajeId);
        $horarios = [$viaje->origen_terminal_id => Carbon::parse($this->fechaSalida.' '.$this->horaSalida)];
        $hora = reset($horarios)->copy();
        foreach ($viaje->tramosConsecutivos() as $tramo) {
            [$horas, $minutos] = explode(':', $tramo->duracion_estimada ?? '00:00:00');
            $hora->addMinutes((int) $horas * 60 + (int) $minutos);
            $horarios[$tramo->destino_terminal_id] = $hora->copy();
        }
        foreach ($viaje->tramos as $tramo) {
            $clave = $tramo->origen_terminal_id.'-'.$tramo->destino_terminal_id;
            $salida = $horarios[$tramo->origen_terminal_id];
            $llegada = $horarios[$tramo->destino_terminal_id];
            $this->tramos[$clave] = [
                'habilitado' => $this->tramos[$clave]['habilitado'] ?? true,
                'precio' => $this->tramos[$clave]['precio'] ?? $tramo->precio,
                'fecha_salida' => $salida->format('Y-m-d'),
                'hora_salida' => $salida->format('H:i'),
                'fecha_llegada' => $llegada->format('Y-m-d'),
                'hora_llegada' => $llegada->format('H:i'),
            ];
        }
    }

    public function save(): void
    {
        Access::authorize('programaciones', $this->programacionId === null ? 'add' : 'edit');
        $programacion = ProgramacionService::guardar($this->usuarioEmpresa->empresa_id, [
            'viaje_id' => $this->viajeId,
            'transporte_id' => $this->transporteId,
            'estatus' => $this->estatus,
            'tramos' => $this->tramos,
        ], $this->programacionId);
        $this->programacionId = $programacion->id;
        if ($this->canList) {
            session()->flash('empresa_success', 'Programación guardada correctamente.');
            $this->redirectRoute('empresas.programaciones.list', navigate: true);
        } else {
            $this->dispatch('successEventList', message: 'Programación guardada correctamente.');
        }
    }

    public function render(): View
    {
        $viajes = Viaje::searchAdmin('', [
            'empresa_id' => $this->usuarioEmpresa->empresa_id,
            'status' => Viaje::ESTADO_ACTIVE,
        ])->with(['tramos.origenTerminal', 'tramos.destinoTerminal'])->get();
        $transportes = Transporte::searchAdmin('', [
            'empresa_id' => $this->usuarioEmpresa->empresa_id,
            'status' => Transporte::ESTADO_ACTIVE,
            'tipo_transporte' => $this->usuarioEmpresa->empresa->getTipoTransporte(),
        ])->where('es_plantilla', false)->get();

        return view('livewire.empresas.programaciones.save-programacion', [
            'viajes' => $viajes,
            'transportes' => $transportes,
            'trayectos' => $viajes->firstWhere('id', $this->viajeId)?->tramos ?? collect(),
        ]);
    }
}
