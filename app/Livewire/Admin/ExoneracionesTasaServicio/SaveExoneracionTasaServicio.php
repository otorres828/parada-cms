<?php

namespace App\Livewire\Admin\ExoneracionesTasaServicio;

use App\Models\Empresa;
use App\Models\ExoneracionTasaServicio;
use App\Services\Admin\Access;
use App\Services\Admin\Audit;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.cms')]
class SaveExoneracionTasaServicio extends Component
{
    #[Locked]
    public ?int $exoneracion_tasa_servicio_id = null;

    public int|string $empresa_id = '';

    public string $fecha_desde = '';

    public string $fecha_hasta = '';

    public string $motivo = '';

    public int|string $estatus = ExoneracionTasaServicio::ACTIVO;

    public function mount(?int $exoneracion_tasa_servicio_id = null): void
    {
        $this->exoneracion_tasa_servicio_id = $exoneracion_tasa_servicio_id;

        if ($exoneracion_tasa_servicio_id !== null) {
            $this->editar(ExoneracionTasaServicio::searchAdmin()->findOrFail($exoneracion_tasa_servicio_id));
        }
    }

    public function render()
    {
        return view('livewire.admin.exoneraciones-tasa-servicio.save-exoneracion-tasa-servicio', [
            'empresas' => Empresa::searchAdmin('', ['status' => 1])->orderBy('nombre')->get(),
        ]);
    }

    public function save()
    {
        Access::authorize(
            'exoneraciones-tasa-servicio',
            $this->exoneracion_tasa_servicio_id ? 'edit' : 'add',
        );

        $data = $this->validate([
            'empresa_id' => ['required', 'integer', 'exists:empresas,id'],
            'fecha_desde' => ['required', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
            'motivo' => ['required', 'string', 'max:255'],
            'estatus' => ['required', 'integer', 'in:1,2'],
        ], [], [
            'empresa_id' => 'Empresa',
            'fecha_desde' => 'Fecha desde',
            'fecha_hasta' => 'Fecha hasta',
            'motivo' => 'Motivo',
            'estatus' => 'Estado',
        ]);

        DB::transaction(function () use ($data) {
            $exoneracion = $this->exoneracion_tasa_servicio_id
                ? ExoneracionTasaServicio::query()->lockForUpdate()->findOrFail($this->exoneracion_tasa_servicio_id)
                : new ExoneracionTasaServicio;

            $data['fecha_hasta'] = $data['fecha_hasta'] ?: null;
            $exoneracion->fill($data);
            $exoneracion->validarSolapamiento();
            $exoneracion->save();
            Audit::record('exoneracion_tasa.guardada', $exoneracion, $data);
        });

        session()->flash('admin_success', 'Exoneración de tasa guardada.');

        return $this->redirect(route('admin.exoneraciones-tasa-servicio.list'), navigate: true);
    }

    protected function editar(ExoneracionTasaServicio $exoneracion): void
    {
        $this->empresa_id = $exoneracion->empresa_id;
        $this->fecha_desde = $exoneracion->fecha_desde->format('Y-m-d\TH:i');
        $this->fecha_hasta = $exoneracion->fecha_hasta?->format('Y-m-d\TH:i') ?? '';
        $this->motivo = $exoneracion->motivo;
        $this->estatus = $exoneracion->estatus;
    }
}
