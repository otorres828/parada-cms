<?php

namespace App\Livewire\Admin\Clientes;

use App\Models\Reserva;
use App\Models\User;
use App\Services\Admin\Access;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.cms')]
class DetailCliente extends Component
{
    #[Locked]
    public ?int $user_id = null;

    public bool $canReservasDetail = false;

    public Collection $reservas;

    public User $user;

    public function mount(?int $user_id = null): void
    {
        Access::authorize('clientes', 'detail');
        $this->user_id = $user_id;
        $this->canReservasDetail = Access::allows('reservas', 'detail');
        $this->reservas = Reserva::searchDetailClient($this->user_id);
        $this->user = $this->findUser();

        if(!$this->user) {
            abort(404);
        }
    }

    public function render()
    {
        return view('livewire.admin.clientes.detail-cliente');
    }

    protected function findUser(): User
    {
        return User::findOrFail($this->user_id);
    }
}
