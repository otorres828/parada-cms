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

    public Auditoria $auditoria;

    public function mount(?int $audit_id = null): void
    {
        $this->audit_id = $audit_id;
        Access::authorize('auditoria', 'detail');
        $this->auditoria = $this->findAuditoria();
    }

    public function render()
    {

        return view('livewire.admin.auditoria.detail-audit');
    }

    protected function findAuditoria(): Auditoria
    {
        return Auditoria::searchAdmin()->with([0 => 'admin'])->findOrFail($this->audit_id);
    }
}
