<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class UpdateSessionOnAuth
{
    /**
     * Handle the event of login.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;

        // get current session id
        $sessionId = session()->getId();

        if (!$sessionId || !$user) {
            return;
        }

        DB::table('sessions')
            ->where('id', $sessionId)
            ->update([
                'authenticatable_type' => get_class($user),
                'authenticatable_id' => $user->getAuthIdentifier(),
            ]);
    }

    /**
     * Optional: handle logout to clear the link
     */
    public function onLogout(Logout $event): void
    {
        $sessionId = session()->getId();
        if (!$sessionId) {
            return;
        }

        DB::table('sessions')
            ->where('id', $sessionId)
            ->update([
                'authenticatable_type' => null,
                'authenticatable_id' => null,
            ]);
    }
}
