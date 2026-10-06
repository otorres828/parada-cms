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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;

#[Layout('layouts.crm')]
class SaveProgramacion extends EmpresaComponent
{
    #[Locked]
    public ?int $programacionId = null;

    #[Locked]
    public Collection $viajes;

    #[Locked]
    public Collection $transportes;

    public bool $canList = false;

    public string $viajeId = '';

    public string $transporteId = '';

    public string $fechaSalida = '';

    public string $horaSalida = '06:00';

    public string $modoFechas = 'unica';

    public string $fechaHasta = '';

    public string $fechaEspecifica = '';

    public array $diasSemana = [1, 2, 3, 4, 5, 6, 7];

    public array $fechasEspecificas = [];

    public int $estatus = 1;

    public array $tramos = [];

    public function mount(?int $programacion_id = null): void
    {
        $this->canList = $this->usuarioEmpresa->hasPermission('programaciones', 'list');
        $this->fechaSalida = today()->format('Y-m-d');

        $this->viajes = Viaje::searchAdmin('', [
            'empresa_id' => $this->usuarioEmpresa->empresa_id,
            'status' => Viaje::ESTADO_ACTIVE,
        ])->with(['tramos.origenTerminal', 'tramos.destinoTerminal'])->get();
        $this->transportes = Transporte::searchAdmin('', [
            'empresa_id' => $this->usuarioEmpresa->empresa_id,
            'status' => Transporte::ESTADO_ACTIVE,
            'tipo_transporte' => $this->usuarioEmpresa->empresa->getTipoTransporte(),
        ])->where('es_plantilla', false)->get();

        if ($programacion_id !== null) {
            $this->editar($this->findProgramacion($programacion_id));
        }
    }

    public function render(): View
    {
        $fechasProgramacion = [];
        if ($this->programacionId === null) {
            try {
                $fechasProgramacion = ProgramacionService::fechas($this->configuracionFechas());
            } catch (ValidationException $exception) {
                // La selección incompleta se valida al guardar; el render no interrumpe el formulario.
            }
        }

        return view('livewire.empresas.programaciones.save-programacion', [
            'capacidadTransporte' => $this->transportes->firstWhere('id', $this->transporteId)?->total_asientos,
            'fechasProgramacion' => $fechasProgramacion,
            'viajes' => $this->viajes,
            'transportes' => $this->transportes,
            'trayectos' => $this->viajes->firstWhere('id', $this->viajeId)?->tramos ?? collect(),
        ]);
    }

    public function save(): void
    {
        Access::authorize('programaciones', $this->programacionId === null ? 'add' : 'edit');
        $datos = [
            'viaje_id' => $this->viajeId,
            'transporte_id' => $this->transporteId,
            'estatus' => $this->estatus,
            'tramos' => $this->tramos,
        ];
        if ($this->programacionId === null) {
            $ids = ProgramacionService::guardarLote($this->usuarioEmpresa->empresa_id, $datos, $this->configuracionFechas());
            $mensaje = count($ids).' programación(es) creada(s) correctamente.';
            $this->reset('tramos', 'viajeId', 'transporteId', 'fechasEspecificas');
        } else {
            ProgramacionService::guardar($this->usuarioEmpresa->empresa_id, $datos, $this->programacionId);
            $mensaje = 'Programación guardada correctamente.';
        }
        if ($this->canList) {
            session()->flash('empresas_programacion_success', $mensaje);
            $this->redirectRoute('empresas.programaciones.list', navigate: true);
        } else {
            $this->dispatch('empresas_programacion_success', message: $mensaje);
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
                'asientos_maximos_permitidos' => $this->tramos[$clave]['asientos_maximos_permitidos'] ?? '',
                'precio' => $this->tramos[$clave]['precio'] ?? $tramo->precio,
                'fecha_salida' => $salida->format('Y-m-d'),
                'hora_salida' => $salida->format('H:i'),
                'fecha_llegada' => $llegada->format('Y-m-d'),
                'hora_llegada' => $llegada->format('H:i'),
            ];
        }
    }

    public function agregarFecha(): void
    {
        $this->validate([
            'fechaEspecifica' => ['required', 'date_format:Y-m-d', 'after_or_equal:today', 'before_or_equal:2100-12-31'],
        ], [
            'required' => 'Selecciona una fecha para añadir.',
            'date_format' => 'La fecha no tiene un formato válido.',
            'after_or_equal' => 'La fecha no puede ser anterior a hoy.',
            'before_or_equal' => 'La fecha supera el año permitido.',
        ]);
        Programacion::exigir(! in_array($this->fechaEspecifica, $this->fechasEspecificas, true), 'fechaEspecifica', 'Esta fecha ya está seleccionada.');
        Programacion::exigir(count($this->fechasEspecificas) < 366, 'fechaEspecifica', 'Puedes añadir hasta 366 fechas.');
        $this->fechasEspecificas[] = $this->fechaEspecifica;
        sort($this->fechasEspecificas);
        $this->fechaEspecifica = '';
    }

    public function removerFecha(int $indice): void
    {
        unset($this->fechasEspecificas[$indice]);
        $this->fechasEspecificas = array_values($this->fechasEspecificas);
    }

    private function configuracionFechas(): array
    {
        return [
            'modo' => $this->modoFechas,
            'desde' => $this->fechaSalida,
            'hasta' => $this->modoFechas === 'rango' ? $this->fechaHasta : null,
            'dias' => $this->modoFechas === 'rango' ? $this->diasSemana : [],
            'fechas' => $this->modoFechas === 'especificas' ? $this->fechasEspecificas : [],
        ];
    }

    protected function editar(Programacion $programacion): void
    {
        abort_unless(in_array($programacion->estatus, [1, 2], true), 401, 'Una programación finalizada no se puede editar.');
        abort_if($programacion->reservas()->exists(), 401, 'Esta programación tiene reservas y no puede editarse.');
        
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
                'asientos_maximos_permitidos' => $tramo->asientos_maximos_permitidos ?? '',
                'precio' => $tramo->precio,
                'fecha_salida' => $tramo->fecha_salida?->format('Y-m-d') ?? '',
                'hora_salida' => substr($tramo->hora_salida ?? '', 0, 5),
                'fecha_llegada' => $tramo->fecha_llegada?->format('Y-m-d') ?? '',
                'hora_llegada' => substr($tramo->hora_llegada ?? '', 0, 5),
            ];
        }
    
    }
}
