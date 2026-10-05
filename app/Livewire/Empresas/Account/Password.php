<?php

namespace App\Livewire\Empresas\Account;

use App\Livewire\Empresas\EmpresaComponent;
use App\Models\UsuarioEmpresa;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;

#[Layout('layouts.crm')]
class Password extends EmpresaComponent
{
    public string $password = '';

    public string $password_confirmation = '';

    public string $current_password = '';

    public function render(): View
    {
        return view('livewire.empresas.account.password');
    }

    public function save(): void
    {

        $data = $this->validate([
            'current_password' => 'required|current_password:empresa',
            'password' => 'required|string|min:10|max:255|confirmed',
        ], [
            'current_password.required' => 'Ingresa tu contraseña actual.',
            'current_password.current_password' => 'La contraseña actual es incorrecta.',
            'password.required' => 'Ingresa la nueva contraseña.',
            'password.string' => 'La nueva contraseña debe ser un texto.',
            'password.min' => 'La nueva contraseña debe tener al menos 10 caracteres.',
            'password.max' => 'La nueva contraseña no debe superar los 255 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ]);

        DB::transaction(function () use ($data) {
            $this->usuarioEmpresa->password = $data['password'];
            $this->usuarioEmpresa->remember_token = Str::random(60);
            $this->usuarioEmpresa->save();

            DB::table('sessions')
                ->where('authenticatable_type', UsuarioEmpresa::class)
                ->where('authenticatable_id', $this->usuarioEmpresa->id)
                ->where('id', '!=', session()->getId())
                ->delete();
        });

        $this->reset('current_password', 'password', 'password_confirmation');
        $this->dispatch('empresas_password_success', message: 'Cuenta actualizada.');
    }
}
