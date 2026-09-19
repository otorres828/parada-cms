<?php

namespace App\Livewire\Admin\Clientes;

use App\Models\User;
use App\Services\Admin\Access;
use App\Services\Admin\Audit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.cms')]
class SaveCliente extends Component
{
    #[Locked]
    public ?int $user_id = null;

    public $name = '';

    public $lastname = '';

    public $email = '';

    public $telefono = '';

    public $status = 1;

    public User $user;

    public function mount(?int $user_id = null): void
    {
        $this->user_id = $user_id;
        Access::authorize('clientes', $this->user_id ? 'edit' : 'add');
        if ($this->user_id) {
            $this->editar($this->findUser());
        }
    }

    public function render()
    {
        return view('livewire.admin.clientes.save-cliente');
    }

    public function save()
    {
        $data = $this->validateForm();

        $user = DB::transaction(function () use ($data) {

            Access::authorize('clientes', $this->user_id ? 'edit' : 'add');
            $user = $this->user_id ? $this->findUser() : new User;
            $user->name = $data['name'];
            $user->lastname = $data['lastname'];
            $user->email = $data['email'];
            $user->telefono = $data['telefono'];
            $user->status = $data['status'];
            $user->save();

            Audit::record($this->user_id ? 'registro.actualizado' : 'registro.creado', $user, $data);

            return $user;
        });
        session()->flash('admin_success', 'Registro guardado correctamente.');
        $target = Route::has('admin.clientes.detail') && Access::allows('clientes', 'detail') ? 'detail' : 'list';
        $url = Access::allows('clientes', $target) ? route('admin.clientes.'.$target, in_array($target, ['list', 'add']) ? [] : ['user_id' => $user->id]) : route('admin.account.profile');

        return $this->redirect($url, navigate: true);
    }

    protected function editar(User $user): void
    {
        $this->user = $user;
        $this->name = $user->name ?? '';
        $this->lastname = $user->lastname ?? '';
        $this->email = $user->email ?? '';
        $this->telefono = $user->telefono ?? '';
        $this->status = (string) (is_bool($user->status) ? (int) $user->status : $user->status);
    }

    protected function validateForm(): array
    {
        $validated = $this->validate(['name' => ['required', 'string', 'max:255'], 'lastname' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user_id)], 'telefono' => ['nullable', 'string', 'max:100'], 'status' => ['required', 'in:1,2']], [], ['name' => 'Nombre', 'lastname' => 'Apellido', 'email' => 'Correo', 'telefono' => 'Teléfono', 'status' => 'Estado']);
        foreach ($validated as $key => &$value) {
            if ($value === '') {
                $value = null;
            }
        }
        unset($value);

        return $validated;
    }

    protected function findUser(): User
    {
        return User::searchAdmin()->with([])->findOrFail($this->user_id);
    }
}
