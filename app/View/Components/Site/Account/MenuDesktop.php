<?php

namespace App\View\Components\Site\Account;

use App\Models\User;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class MenuDesktop extends Component
{
    /**
     * Create a new component instance.
     */

    public User $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.site.account.menu-desktop');
    }
}
