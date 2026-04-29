<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

class TrackUserLogin
{
    /**
     * Update login count and last login timestamp on every successful login.
     */
    public function handle(Login $event): void
    {
        $event->user->increment('login_count');
        $event->user->last_login_at = now();
        $event->user->saveQuietly(); // Skip model events / timestamps update
    }
}