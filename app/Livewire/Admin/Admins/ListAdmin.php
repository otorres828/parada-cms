<?php

namespace App\Livewire\Admin\Admins;

use App\Models\Admin;
use App\Traits\Listing;
use App\Traits\Permissions;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.cms')]
class ListAdmin extends Component
{
    use Listing;
    use Permissions;
    use WithPagination;

    public string $status = '';

    protected array $queryString = [
        'search' => ['except' => ''],
        'per_page' => ['except' => 10],
        'status' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->sortColumn = 'id';
        $this->sortDirection = 'desc';
        $this->checkPermissions('admins');
    }

    public function render()
    {
        $query = Admin::searchAdmin($this->search, [
            'status' => $this->status,
        ]);

        $query = $this->applySort($query);

        $admins = $query->paginate($this->per_page);

        return view('livewire.admin.admins.list-admin', [
            'admins' => $admins,
        ]);
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'status', 'per_page'])) {
            $this->resetPage();
        }
    }
}
