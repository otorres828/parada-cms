<?php

namespace App\Livewire\Admin\Amenidades;

use App\Models\Amenidad;
use App\Services\Admin\Access;
use App\Services\Admin\Audit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.cms')]
class SaveAmenidad extends Component
{
    #[Locked]
    public ?int $amenidad_id = null;

    public $nombre = '';

    public $icono = '';

    public $estatus = 1;

    public function mount(?int $amenidad_id = null): void
    {
        $this->amenidad_id = $amenidad_id;
        Access::authorize('amenidades', $this->amenidad_id ? 'edit' : 'add');
        if ($this->amenidad_id) {
            $this->editar($this->findAmenidad());
        }
    }

    public function render()
    {
        Access::authorize('amenidades', $this->amenidad_id ? 'edit' : 'add');

        return view('livewire.admin.amenidades.save-amenidad', ['amenidad' => $this->amenidad_id ? $this->findAmenidad() : null, 'capabilities' => Access::capabilities('amenidades')]);
    }

    public function save()
    {
        Access::authorize('amenidades', $this->amenidad_id ? 'edit' : 'add');
        $data = $this->validateForm();
        $amenidad = DB::transaction(function () use ($data) {
            Access::authorize('amenidades', $this->amenidad_id ? 'edit' : 'add');
            $amenidad = $this->amenidad_id ? $this->findAmenidad() : new Amenidad;
            $amenidad->nombre = $data['nombre'];
            $amenidad->icono = $data['icono'];
            $amenidad->estatus = $data['estatus'];
            $amenidad->save();
            Audit::record($this->amenidad_id ? 'registro.actualizado' : 'registro.creado', $amenidad, $data);

            return $amenidad;
        });
        session()->flash('admin_success', 'Registro guardado correctamente.');
        $target = Route::has('admin.amenidades.detail') && Access::allows('amenidades', 'detail') ? 'detail' : 'list';
        $url = Access::allows('amenidades', $target) ? route('admin.amenidades.'.$target, in_array($target, ['list', 'add']) ? [] : ['amenidad_id' => $amenidad->id]) : route('admin.account.profile');

        return $this->redirect($url, navigate: true);
    }

    protected function editar(Amenidad $amenidad): void
    {
        $this->nombre = $amenidad->nombre ?? '';
        $this->icono = $amenidad->icono ?? '';
        $this->estatus = (string) (is_bool($amenidad->estatus) ? (int) $amenidad->estatus : $amenidad->estatus);
    }

    protected function validateForm(): array
    {
        $validated = $this->validate(['nombre' => ['required', 'string', 'max:255'], 'icono' => ['required', 'string', 'max:64', 'regex:/^bi-[a-z0-9-]+$/'], 'estatus' => ['required', 'in:0,1']], [], ['nombre' => 'Nombre', 'icono' => 'Ícono Bootstrap (ej. bi-wifi)', 'estatus' => 'Estado']);
        foreach ($validated as $key => &$value) {
            if ($value === '') {
                $value = null;
            }
        }
        unset($value);

        return $validated;
    }

    protected function findAmenidad(): Amenidad
    {
        return Amenidad::searchAdmin()->with([])->findOrFail($this->amenidad_id);
    }
}
