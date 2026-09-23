<?php

namespace App\Livewire\Admin\Admins;

use App\Models\Admin;
use App\Models\GroupAdmin;
use App\Models\PermissionAdmin;
use App\Services\Admin\Access;
use App\Services\Admin\Audit;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.cms')]
class SaveAdmin extends Component
{
    #[Locked]
    public ?int $admin_id = null;

    public string $name = '';

    public string $username = '';

    public string $email = '';

    public string $password = '';

    public $status = 1;

    public bool $is_superadmin = false;

    public array $selectedPermissions = [];

    public function mount(?int $admin_id = null): void
    {
        $this->admin_id = $admin_id;
        Access::authorize('admins', $admin_id ? 'edit' : 'add');
        if ($admin_id) {
            $this->editar(Admin::searchAdmin()->findOrFail($admin_id));
        }
    }

    public function render()
    {
        $groups = GroupAdmin::where('status', 1)->with(['sections' => fn ($q) => $q->where('status', 1)->where('url', '!=', 'admins'), 'sections.permissions' => fn ($q) => $q->where('status', 1)])->orderBy('id')->get();

        return view('livewire.admin.admins.save-admin', ['groups' => $groups, 'editingRoot' => $this->admin_id && Admin::findOrFail($this->admin_id)->isRoot()]);
    }

    public function save()
    {
        Access::authorize('admins', $this->admin_id ? 'edit' : 'add');
        $this->validate(['name' => 'required|string|max:255', 'username' => ['required', 'string', 'min:3', 'max:100', Rule::unique('admins', 'username')->ignore($this->admin_id)], 'email' => ['required', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($this->admin_id)], 'password' => [$this->admin_id ? 'nullable' : 'required', 'string', 'min:10', 'max:255'], 'status' => 'required|in:1,2', 'is_superadmin' => 'boolean', 'selectedPermissions' => 'array', 'selectedPermissions.*' => 'integer|distinct|exists:permissions_admin,id']);
        DB::transaction(function () {
            Admin::orderBy('id')->lockForUpdate()->get();
            Access::authorize('admins', $this->admin_id ? 'edit' : 'add');
            $admin = $this->admin_id ? Admin::searchAdmin()->findOrFail($this->admin_id) : new Admin;
            $level = $admin->exists && $admin->isRoot() ? Admin::ROOT : ($this->is_superadmin ? Admin::SUPERADMIN : Admin::ADMIN);
            if ($admin->id === auth('admin')->id() && (int) $this->status !== Admin::ACTIVO) {
                throw ValidationException::withMessages(['status' => 'No puedes desactivar tu propia cuenta.']);
            }
            $ids = [];
            if ($level === Admin::ADMIN) {
                $ids = PermissionAdmin::query()->whereIn('id', $this->selectedPermissions)->where('status', 1)->whereHas('section', fn ($q) => $q->where('status', 1)->where('url', '!=', 'admins')->whereHas('group', fn ($g) => $g->where('status', 1)))->pluck('id')->all();
                if (count($ids) !== count($this->selectedPermissions)) {
                    throw ValidationException::withMessages(['selectedPermissions' => 'Hay permisos inactivos o reservados para el root.']);
                }
            }
            $admin->name = $this->name;
            $admin->username = $this->username;
            $admin->email = $this->email;
            $admin->level = $level;
            $admin->status = $this->status;
            if ($this->password !== '') {
                $admin->password = $this->password;
            }
            $admin->save();
            if (! Admin::where('level', Admin::ROOT)->where('status', Admin::ACTIVO)->exists()) {
                throw ValidationException::withMessages(['status' => 'Debe existir al menos un root activo.']);
            }
            $admin->permissions()->sync(array_fill_keys($ids, ['status' => 1]));
            Audit::record($this->admin_id ? 'administrador.actualizado' : 'administrador.creado', $admin, ['level' => $level, 'permission_ids' => $ids]);
        });
        session()->flash('admin_success', 'Administrador y permisos guardados correctamente.');

        return $this->redirect(route('admin.admins.list'), navigate: true);
    }

    protected function editar(Admin $admin): void
    {
        $this->name = $admin->name;
        $this->username = $admin->username;
        $this->email = $admin->email;
        $this->status = $admin->status;
        $this->is_superadmin = $admin->isSuperAdmin();
        $this->selectedPermissions = $admin->permissions()->wherePivot('status', 1)->pluck('permissions_admin.id')->map(fn ($id) => (string) $id)->all();
    }
}
