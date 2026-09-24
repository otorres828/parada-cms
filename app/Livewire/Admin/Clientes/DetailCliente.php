<?php

namespace App\Livewire\Admin\Clientes;

use App\Models\Reserva;
use App\Models\User;
use App\Services\Admin\Access;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.cms')]
class DetailCliente extends Component
{
    #[Locked]
    public ?int $user_id = null;

    public bool $canReservasDetail = false;

    use WithPagination;

    public int $per_page = 10;

    protected string $paginationTheme = 'bootstrap';

    public User $user;

    public function mount(?int $user_id = null): void
    {
        $this->user_id = $user_id;
        $this->canReservasDetail = Access::allows('reservas', 'detail');
        $this->user = $this->findUser();

        if (! $this->user) {
            abort(404);
        }
    }

    public function render()
    {
        return view('livewire.admin.clientes.detail-cliente', ['reservas' => Reserva::searchDetailClient($this->user_id)->paginate(max(1, min(100, $this->per_page)))]);
    }

    protected function findUser(): User
    {
        return User::findOrFail($this->user_id);
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }
}
