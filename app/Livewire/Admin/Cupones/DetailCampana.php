<?php

namespace App\Livewire\Admin\Cupones;

use App\Models\ConfiguracionCupon;
use App\Models\Cupon;
use App\Services\Admin\Access;
use App\Services\Admin\Audit;
use App\Traits\Listing;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.cms')]
class DetailCampana extends Component
{
    use Listing, WithPagination;

    public string $status = '';

    protected array $queryString = [
        'search' => ['except' => ''], 
        'status' => ['except' => ''], 
        'per_page' => ['except' => 10]
    ];

    #[Locked]
    public ?int $configuracion_cupon_id = null;

    public function mount(?int $configuracion_cupon_id = null): void
    {
        $this->configuracion_cupon_id = $configuracion_cupon_id;
        $this->sortColumn = 'id';
        $this->sortDirection = 'desc';
        Access::authorize('cupones', 'detail');
    }

    public function render()
    {
        Access::authorize('cupones', 'detail');
        $query = Cupon::searchAdmin($this->search, ['configuracion_cupon_id' => $this->configuracion_cupon_id, 'status' => $this->status]);
        $cupones = $this->applySort($query)->paginate($this->per_page);

        return view('livewire.admin.cupones.detail-campana', ['cupones' => $cupones, 'configuracionCupon' => $this->configuracion_cupon_id ? $this->findConfiguracionCupon() : null]);
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'status', 'per_page'])) {
            $this->resetPage();
        }
    }

    public function generateCoupons(): void
    {
        abort_unless($this->configuracion_cupon_id, 403);
        Access::authorize('cupones', 'edit');
        DB::transaction(function () {
            $campaign = ConfiguracionCupon::whereKey($this->configuracion_cupon_id)->lockForUpdate()->firstOrFail();
            if ($campaign->cupones()->exists()) {
                throw ValidationException::withMessages(['cupones' => 'Esta campaña ya tiene cupones generados.']);
            }
            if (! $campaign->estatus || $campaign->fecha_fin->isPast()) {
                throw ValidationException::withMessages(['cupones' => 'La campaña está inactiva o vencida.']);
            }
            for ($i = 0; $i < $campaign->cantidad_generar; $i++) {
                Cupon::create(['configuracion_cupon_id' => $campaign->id, 'codigo' => strtoupper($campaign->codigo_base).'-'.strtoupper(bin2hex(random_bytes(6))), 'redimido' => false]);
            }
            Audit::record('cupones.generados', $campaign, ['cantidad' => $campaign->cantidad_generar]);
        });
        $this->dispatch('successEventList', message: 'Cupones generados.');
    }

    protected function findConfiguracionCupon(): ConfiguracionCupon
    {
        return ConfiguracionCupon::searchAdmin()->findOrFail($this->configuracion_cupon_id);
    }
}
