<?php

namespace App\Livewire;

use App\Models\SolicitudEmpresa;
use App\Support\ActionRateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.home')]
class Home extends Component
{
    public string $nombre = '';

    public string $empresa = '';

    public string $cargo = '';

    public string $telefono = '';

    public string $email = '';

    public string $ciudad = '';

    public string $mensaje = '';

    public string $website = '';

    public bool $solicitudEnviada = false;

    public function render()
    {
        return view('livewire.home');
    }

    public function enviarSolicitud(): void
    {
        if ($this->website !== '') {
            $this->resetFormulario();

            return;
        }

        ActionRateLimiter::validarRateLimit();

        $datos = $this->validate([
            'nombre' => 'required|string|max:255',
            'empresa' => 'required|string|max:255',
            'cargo' => 'required|string|max:150',
            'telefono' => 'required|string|max:50',
            'email' => 'required|email:rfc|max:255',
            'ciudad' => 'nullable|string|max:150',
            'mensaje' => 'nullable|string|max:1500',
        ], [
            'required' => 'El campo :attribute es obligatorio.',
            'email' => 'El campo :attribute debe contener un correo válido.',
            'max' => 'El campo :attribute no puede superar :max caracteres.',
        ], [
            'nombre' => 'nombre y apellido',
            'empresa' => 'agencia o empresa',
            'cargo' => 'cargo',
            'telefono' => 'teléfono',
            'email' => 'correo electrónico',
            'ciudad' => 'ciudad',
            'mensaje' => 'mensaje',
        ]);

        SolicitudEmpresa::create([
            ...$datos,
            'estatus' => SolicitudEmpresa::ESTADO_NUEVA,
        ]);

        $this->resetFormulario();
        $this->solicitudEnviada = true;
    }

    protected function resetFormulario(): void
    {
        $this->reset([
            'nombre',
            'empresa',
            'cargo',
            'telefono',
            'email',
            'ciudad',
            'mensaje',
            'website',
        ]);
    }
}
