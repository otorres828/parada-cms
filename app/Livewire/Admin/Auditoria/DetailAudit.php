<?php

namespace App\Livewire\Admin\Auditoria;

use App\Models\Auditoria;
use App\Services\Admin\Access;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.cms')]
class DetailAudit extends Component
{
    #[Locked]
    public ?int $audit_id = null;

    public function mount(?int $audit_id = null): void
    {
        $this->audit_id = $audit_id;
        Access::authorize('auditoria', 'detail');
        $auditoria = $this->findAuditoria();
    }

    public function render()
    {
        Access::authorize('auditoria', 'detail');

        return view('livewire.admin.auditoria.detail-audit', ['auditoria' => $this->audit_id ? $this->findAuditoria() : null, 'capabilities' => Access::capabilities('auditoria')]);
    }

    protected function findAuditoria(): Auditoria
    {
        return Auditoria::searchAdmin()->with([0 => 'admin'])->findOrFail($this->audit_id);
    }
}
