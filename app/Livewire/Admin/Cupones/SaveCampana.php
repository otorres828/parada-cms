<?php

namespace App\Livewire\Admin\Cupones;

use App\Models\ConfiguracionCupon;
use App\Models\Empresa;
use App\Services\Admin\Access;
use App\Services\Admin\Audit;
use App\Services\CuponService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.cms')]
class SaveCampana extends Component
{
    public ?ConfiguracionCupon $configuracionCupon = null;

    #[Locked]
    public ?int $configuracion_cupon_id = null;

    public int|string $empresa_id = '';

    public string $search_empresa_id = '';

    public string $nombre_campana = '';

    public string $codigo_personalizado = '';

    public int|string $tipo_cupon = ConfiguracionCupon::TIPO_RANDOM;

    public string $modalidad = ConfiguracionCupon::MODALIDAD_GENERAL;

    public string $aplica_en = ConfiguracionCupon::APLICA_EN_RESERVA;

    public int|string $cantidad_generar = 1;

    public string $tipo_descuento = 'porcentaje';

    public string $monto_descuento = '';

    public string $fecha_inicio = '';

    public string $fecha_fin = '';

    public int|string $estatus = 1;

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
        DB::transaction(function () use ($data) {
            Access::authorize('cupones', $this->configuracion_cupon_id ? 'edit' : 'add');
            $esNuevo = $this->configuracion_cupon_id === null;
            $configuracionCupon = $this->configuracion_cupon_id ? $this->findConfiguracionCupon() : new ConfiguracionCupon;
            if ($this->configuracion_cupon_id && $configuracionCupon->cupones()->exists()) {
                foreach (['empresa_id', 'codigo_personalizado', 'cantidad_generar', 'tipo_cupon', 'tipo_descuento', 'aplica_en', 'monto_descuento', 'fecha_inicio'] as $immutable) {
                    unset($data[$immutable]);
                }
            }
            if (array_key_exists('empresa_id', $data)) {
                $configuracionCupon->empresa_id = $data['empresa_id'];
            }
            if (array_key_exists('nombre_campana', $data)) {
                $configuracionCupon->nombre_campana = $data['nombre_campana'];
            }
            if (array_key_exists('codigo_personalizado', $data)) {
                $configuracionCupon->codigo_personalizado = $data['codigo_personalizado'];
            }
            if (array_key_exists('tipo_cupon', $data)) {
                $configuracionCupon->tipo_cupon = $data['tipo_cupon'];
            }
            if (array_key_exists('modalidad', $data)) {
                $configuracionCupon->modalidad = $data['modalidad'];
            }
            if (array_key_exists('aplica_en', $data)) {
                $configuracionCupon->aplica_en = $data['aplica_en'];
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

            if ($esNuevo) {
                app(CuponService::class)->crearCuponesAleatorios($configuracionCupon);
            }

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
        $this->codigo_personalizado = $configuracionCupon->codigo_personalizado ?? '';
        $this->tipo_cupon = $configuracionCupon->tipo_cupon;
        $this->modalidad = $configuracionCupon->modalidad;
        $this->aplica_en = $configuracionCupon->aplica_en;
        $this->cantidad_generar = $configuracionCupon->cantidad_generar ?? '';
        $this->tipo_descuento = (string) (is_bool($configuracionCupon->tipo_descuento) ? (int) $configuracionCupon->tipo_descuento : $configuracionCupon->tipo_descuento);
        $this->monto_descuento = $configuracionCupon->monto_descuento ?? '';
        $this->fecha_inicio = $configuracionCupon->fecha_inicio?->format('Y-m-d\TH:i') ?? '';
        $this->fecha_fin = $configuracionCupon->fecha_fin?->format('Y-m-d\TH:i') ?? '';
        $this->estatus = (string) (is_bool($configuracionCupon->estatus) ? (int) $configuracionCupon->estatus : $configuracionCupon->estatus);
    }

    protected function validateForm(): array
    {
        $validated = $this->validate([
            'empresa_id' => ['nullable', 'integer', 'exists:empresas,id'],
            'nombre_campana' => ['required', 'string', 'max:255'],
            'tipo_cupon' => ['required', 'integer', 'in:1,2'],
            'modalidad' => ['required', 'in:GENERAL,PRIMERA_COMPRA,USUARIO_NUEVO'],
            'aplica_en' => ['required', 'in:reserva,pasajes'],
            'codigo_personalizado' => ['required_if:tipo_cupon,2', 'nullable', 'string', 'max:100', 'alpha_dash', Rule::unique('configuracion_cupones', 'codigo_personalizado')->ignore($this->configuracion_cupon_id)],
            'cantidad_generar' => ['required', 'integer', 'min:1', 'max:1000'],
            'tipo_descuento' => ['required', 'in:porcentaje,monto_fijo'],
            'monto_descuento' => ['required', 'decimal:0,2', 'min:0.01', 'max:999999.99'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after:fecha_inicio'],
            'estatus' => ['required', 'in:0,1,2'],
        ], [], ['empresa_id' => 'Empresa', 'nombre_campana' => 'Nombre', 'tipo_cupon' => 'Tipo de cupón', 'modalidad' => 'Modalidad', 'aplica_en' => 'Aplicación del descuento', 'codigo_personalizado' => 'Código personalizado', 'cantidad_generar' => 'Cantidad de cupones', 'tipo_descuento' => 'Descuento', 'monto_descuento' => 'Valor del descuento', 'fecha_inicio' => 'Inicio', 'fecha_fin' => 'Fin', 'estatus' => 'Estado']);
        foreach ($validated as $key => &$value) {
            if ($value === '') {
                $value = null;
            }
        }
        unset($value);
        if ($validated['tipo_descuento'] === 'porcentaje' && $validated['monto_descuento'] > 100) {
            throw ValidationException::withMessages(['monto_descuento' => 'El porcentaje no puede superar 100.']);
        }

        if ((int) $validated['tipo_cupon'] === ConfiguracionCupon::TIPO_PERSONALIZADO) {
            $validated['codigo_personalizado'] = strtoupper($validated['codigo_personalizado']);
        } else {
            $validated['codigo_personalizado'] = null;
        }

        return $validated;
    }

    protected function findConfiguracionCupon(): ConfiguracionCupon
    {
        return ConfiguracionCupon::searchAdmin()->with([0 => 'empresa', 1 => 'cupones'])->findOrFail($this->configuracion_cupon_id);
    }
}
