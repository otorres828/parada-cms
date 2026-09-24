<?php

namespace App\Livewire\Admin\Solicitudes;

use App\Models\SolicitudEmpresa;
use App\Services\Admin\Access;
use App\Services\Admin\Audit;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.cms')]
class DetailSolicitud extends Component
{
    #[Locked]
    public int $solicitud_id;

    public int $estatus = SolicitudEmpresa::ESTADO_NUEVA;

    public bool $canEdit = false;

    public function mount(int $solicitud_id): void
    {
        $this->solicitud_id = $solicitud_id;
        $solicitud = SolicitudEmpresa::findOrFail($this->solicitud_id);
        $this->estatus = $solicitud->estatus;
        $this->canEdit = Access::allows('solicitudes', 'edit');
    }

    public function render()
    {
        return view('livewire.admin.solicitudes.detail-solicitud', [
            'solicitud' => SolicitudEmpresa::findOrFail($this->solicitud_id),
        ]);
    }

    public function save(): void
    {
        Access::authorize('solicitudes', 'edit');

        $this->validate([
            'estatus' => [
                'required',
                'integer',
                Rule::in([
                    SolicitudEmpresa::ESTADO_NUEVA,
                    SolicitudEmpresa::ESTADO_CONTACTADA,
                    SolicitudEmpresa::ESTADO_CERRADA,
                ]),
            ],
        ]);

        $solicitud = SolicitudEmpresa::findOrFail($this->solicitud_id);
        $solicitud->estatus = $this->estatus;
        $solicitud->save();

        Audit::record('solicitud.estado', $solicitud, [
            'estatus' => $solicitud->estatus,
        ]);

        $this->dispatch('successEventList', message: 'Estado de la solicitud actualizado.');
    }
}
