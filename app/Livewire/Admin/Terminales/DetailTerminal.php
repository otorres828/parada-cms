<?php

namespace App\Livewire\Admin\Terminales;

use App\Models\Terminal;
use App\Services\Admin\Access;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.cms')]
class DetailTerminal extends Component
{
    #[Locked]
    public ?int $terminal_id = null;

    public function mount(?int $terminal_id = null): void
    {
        $this->terminal_id = $terminal_id;
        Access::authorize('terminales', 'detail');
        $terminal = $this->findTerminal();
    }

    public function render()
    {
        Access::authorize('terminales', 'detail');

        return view('livewire.admin.terminales.detail-terminal', ['terminal' => $this->terminal_id ? $this->findTerminal() : null, 'capabilities' => Access::capabilities('terminales')]);
    }

    protected function findTerminal(): Terminal
    {
        return Terminal::searchAdmin()->with([0 => 'estado'])->findOrFail($this->terminal_id);
    }
}
