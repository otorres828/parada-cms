<?php

namespace App\Livewire\Admin\Account;

use App\Models\Admin;
use App\Services\Admin\Access;
use App\Services\Admin\Audit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.cms')]
class Password extends Component
{
    public string $password = '';

    public string $password_confirmation = '';

    public string $current_password = '';

    public function mount(): void
    {
        Access::authorize('account', 'password');
        $admin = auth('admin')->user();
    }

    public function render()
    {
        return view('livewire.admin.account.password', ['mode' => 'password']);
    }

    public function save(): void
    {
        Access::authorize('account', 'password');
        $admin = Admin::findOrFail(auth('admin')->id());
        $rules = ['current_password' => 'required|current_password:admin'];
        $rules['password'] = 'required|string|min:10|max:255|confirmed';
        $data = $this->validate($rules);
        unset($data['current_password']);
        DB::transaction(function () use ($admin, $data) {
            $admin->fill($data);
            $admin->remember_token = Str::random(60);
            DB::table('sessions')->where('authenticatable_type', Admin::class)->where('authenticatable_id', $admin->id)->where('id', '!=', session()->getId())->delete();
            $admin->save();
            Audit::record('cuenta.'.'password', $admin);
        });
        $this->reset('current_password', 'password', 'password_confirmation');
        $this->dispatch('successEventList', message: 'Cuenta actualizada.');
    }
}
