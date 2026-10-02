<?php

namespace App\Livewire\Empresas\Account;

use App\Livewire\Empresas\EmpresaComponent;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;

#[Layout('layouts.crm')]
class Profile extends EmpresaComponent
{
    public string $nombre = '';

    public string $email = '';

    public string $current_password = '';

    public function mount(): void
    {
        $this->nombre = $this->usuarioEmpresa->nombre;
        $this->email = $this->usuarioEmpresa->email;
    }

    public function render(): View
    {
        return view('livewire.empresas.account.profile');
    }

    public function save(): void
    {
        $data = $this->validate([
            'current_password' => 'required|current_password:empresa',
            'nombre' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('usuarios_empresa', 'email')->ignore($this->usuarioEmpresa->id)],
        ], [
            'current_password.required' => 'Ingresa tu contraseña actual.',
            'current_password.current_password' => 'La contraseña actual es incorrecta.',
            'nombre.required' => 'Ingresa tu nombre.',
            'nombre.string' => 'El nombre debe ser un texto.',
            'nombre.max' => 'El nombre no debe superar los 255 caracteres.',
            'email.required' => 'Ingresa tu correo electrónico.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'email.max' => 'El correo electrónico no debe superar los 255 caracteres.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
        ]);

        unset($data['current_password']);

        $this->usuarioEmpresa->fill($data);
        $this->usuarioEmpresa->save();

        $this->reset('current_password');
        $this->dispatch('successEventList', message: 'Cuenta actualizada.');
    }
}
