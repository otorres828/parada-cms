<?php

namespace App\Livewire\Admin\Account;

use App\Models\Admin;
use App\Services\Admin\Audit;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.cms')]
class Profile extends Component
{
    public string $name = '';

    public string $username = '';

    public string $email = '';

    public string $current_password = '';

    public function mount(): void
    {
        $admin = auth('admin')->user();
        $this->name = $admin->name;
        $this->email = $admin->email;
        $this->username = $admin->username;
    }

    public function render()
    {
        return view('livewire.admin.account.profile', ['mode' => 'profile']);
    }

    public function save(): void
    {

        $admin = Admin::findOrFail(auth('admin')->id());
        $rules = [
            'current_password' => 'required|current_password:admin',
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('admins')->ignore($admin->id)],
            'username' => ['required', 'string', 'min:3', 'max:100', Rule::unique('admins')->ignore($admin->id)],
        ];
        $data = $this->validate($rules);
        unset($data['current_password']);
        DB::transaction(function () use ($admin, $data) {
            $admin->fill($data);
            $admin->save();
            Audit::record('cuenta.'.'profile', $admin);
        });
        $this->reset('current_password');
        $this->dispatch('successEventList', message: 'Cuenta actualizada.');
    }
}
