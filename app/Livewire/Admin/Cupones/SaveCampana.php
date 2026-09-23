<?php

namespace App\Livewire\Admin\Cupones;

use App\Models\ConfiguracionCupon;
use App\Models\Empresa;
use App\Services\Admin\Access;
use App\Services\Admin\Audit;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.cms')]
class SaveCampana extends Component
{
    public ConfiguracionCupon $configuracionCupon;

    #[Locked]
    public ?int $configuracion_cupon_id = null;

    public $empresa_id = '';

    public string $search_empresa_id = '';

    public $nombre_campana = '';

    public $codigo_base = '';

    public $tipo_cupon = 'unico';

    public $modalidad = 'codigo';

    public $cantidad_generar = 1;

    public $tipo_descuento = 'porcentaje';

    public $monto_descuento = '';

    public $aplica_a = 'pasajes';

    public $fecha_inicio = '';

    public $fecha_fin = '';

    public $estatus = 1;

    public function mount(?int $configuracion_cupon_id = null): void
    {
        $this->configuracion_cupon_id = $configuracion_cupon_id;
        Access::authorize('cupones', $this->configuracion_cupon_id ? 'edit' : 'add');
        if ($this->configuracion_cupon_id) {
            $this->editar($this->findConfiguracionCupon());
        }
    }

    public function render()
    {
        Access::authorize('cupones', $this->configuracion_cupon_id ? 'edit' : 'add');
        $query = Empresa::searchAdmin($this->search_empresa_id);
        $options_empresa_id = (clone $query)->orderBy('nombre')->limit(100)->pluck('nombre', 'id')->all();
        if ($this->empresa_id && ! isset($options_empresa_id[$this->empresa_id])) {
            $selected = Empresa::searchAdmin()->find($this->empresa_id);
            if ($selected) {
                $options_empresa_id[$selected->id] = $selected->nombre;
            }
        }

        return view('livewire.admin.cupones.save-campana', ['options_empresa_id' => $options_empresa_id]);
    }

    public function save()
    {
        Access::authorize('cupones', $this->configuracion_cupon_id ? 'edit' : 'add');
        $data = $this->validateForm();
        $configuracionCupon = DB::transaction(function () use ($data) {
            Access::authorize('cupones', $this->configuracion_cupon_id ? 'edit' : 'add');
            $configuracionCupon = $this->configuracion_cupon_id ? $this->findConfiguracionCupon() : new ConfiguracionCupon;
            if ($this->configuracion_cupon_id && $configuracionCupon->cupones()->exists()) {
                foreach (['empresa_id', 'codigo_base', 'cantidad_generar', 'tipo_descuento', 'monto_descuento', 'fecha_inicio'] as $immutable) {
                    unset($data[$immutable]);
                }
            }
            if (array_key_exists('empresa_id', $data)) {
                $configuracionCupon->empresa_id = $data['empresa_id'];
            }
            if (array_key_exists('nombre_campana', $data)) {
                $configuracionCupon->nombre_campana = $data['nombre_campana'];
            }
            if (array_key_exists('codigo_base', $data)) {
                $configuracionCupon->codigo_base = $data['codigo_base'];
            }
            if (array_key_exists('tipo_cupon', $data)) {
                $configuracionCupon->tipo_cupon = $data['tipo_cupon'];
            }
            if (array_key_exists('modalidad', $data)) {
                $configuracionCupon->modalidad = $data['modalidad'];
            }
            if (array_key_exists('cantidad_generar', $data)) {
                $configuracionCupon->cantidad_generar = $data['cantidad_generar'];
            }
            if (array_key_exists('tipo_descuento', $data)) {
                $configuracionCupon->tipo_descuento = $data['tipo_descuento'];
            }
            if (array_key_exists('monto_descuento', $data)) {
                $configuracionCupon->monto_descuento = $data['monto_descuento'];
            }
            if (array_key_exists('aplica_a', $data)) {
                $configuracionCupon->aplica_a = $data['aplica_a'];
            }
            if (array_key_exists('fecha_inicio', $data)) {
                $configuracionCupon->fecha_inicio = $data['fecha_inicio'];
            }
            if (array_key_exists('fecha_fin', $data)) {
                $configuracionCupon->fecha_fin = $data['fecha_fin'];
            }
            if (array_key_exists('estatus', $data)) {
                $configuracionCupon->estatus = $data['estatus'];
            }
            $configuracionCupon->save();
            Audit::record($this->configuracion_cupon_id ? 'registro.actualizado' : 'registro.creado', $configuracionCupon, $data);

            return $configuracionCupon;
        });
        session()->flash('admin_success', 'Registro guardado correctamente.');

        return $this->redirect(route('admin.cupones.list'), navigate: true);
    }

    protected function editar(ConfiguracionCupon $configuracionCupon): void
    {
        $this->configuracionCupon = $configuracionCupon;
        $this->empresa_id = $configuracionCupon->empresa_id ?? '';
        $this->nombre_campana = $configuracionCupon->nombre_campana ?? '';
        $this->codigo_base = $configuracionCupon->codigo_base ?? '';
        $this->tipo_cupon = (string) (is_bool($configuracionCupon->tipo_cupon) ? (int) $configuracionCupon->tipo_cupon : $configuracionCupon->tipo_cupon);
        $this->modalidad = (string) (is_bool($configuracionCupon->modalidad) ? (int) $configuracionCupon->modalidad : $configuracionCupon->modalidad);
        $this->cantidad_generar = $configuracionCupon->cantidad_generar ?? '';
        $this->tipo_descuento = (string) (is_bool($configuracionCupon->tipo_descuento) ? (int) $configuracionCupon->tipo_descuento : $configuracionCupon->tipo_descuento);
        $this->monto_descuento = $configuracionCupon->monto_descuento ?? '';
        $this->aplica_a = (string) (is_bool($configuracionCupon->aplica_a) ? (int) $configuracionCupon->aplica_a : $configuracionCupon->aplica_a);
        $this->fecha_inicio = $configuracionCupon->fecha_inicio?->format('Y-m-d\TH:i') ?? '';
        $this->fecha_fin = $configuracionCupon->fecha_fin?->format('Y-m-d\TH:i') ?? '';
        $this->estatus = (string) (is_bool($configuracionCupon->estatus) ? (int) $configuracionCupon->estatus : $configuracionCupon->estatus);
    }

    protected function validateForm(): array
    {
        $validated = $this->validate(['empresa_id' => ['nullable', 'integer', 'exists:empresas,id'], 'nombre_campana' => ['required', 'string', 'max:255'], 'codigo_base' => ['required', 'string', 'max:30', 'alpha_dash'], 'tipo_cupon' => ['required', 'in:unico'], 'modalidad' => ['required', 'in:codigo'], 'cantidad_generar' => ['required', 'integer', 'min:1', 'max:1000'], 'tipo_descuento' => ['required', 'in:porcentaje,fijo'], 'monto_descuento' => ['required', 'decimal:0,2', 'min:0.01', 'max:999999.99'], 'aplica_a' => ['required', 'in:pasajes'], 'fecha_inicio' => ['required', 'date'], 'fecha_fin' => ['required', 'date', 'after:fecha_inicio'], 'estatus' => ['required', 'in:0,1']], [], ['empresa_id' => 'Empresa (vacío para campaña general)', 'nombre_campana' => 'Nombre', 'codigo_base' => 'Prefijo del código', 'tipo_cupon' => 'Tipo de cupón', 'modalidad' => 'Modalidad', 'cantidad_generar' => 'Cantidad de cupones', 'tipo_descuento' => 'Descuento', 'monto_descuento' => 'Valor del descuento', 'aplica_a' => 'Aplicar a', 'fecha_inicio' => 'Inicio', 'fecha_fin' => 'Fin', 'estatus' => 'Estado']);
        foreach ($validated as $key => &$value) {
            if ($value === '') {
                $value = null;
            }
        }
        unset($value);
        if ($validated['tipo_descuento'] === 'porcentaje' && $validated['monto_descuento'] > 100) {
            throw ValidationException::withMessages(['monto_descuento' => 'El porcentaje no puede superar 100.']);
        }

        return $validated;
    }

    protected function findConfiguracionCupon(): ConfiguracionCupon
    {
        return ConfiguracionCupon::searchAdmin()->with([0 => 'empresa', 1 => 'cupones'])->findOrFail($this->configuracion_cupon_id);
    }
}
