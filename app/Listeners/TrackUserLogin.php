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
        $user = $event->user;

        // Skip if this user already logged in within the last 60 seconds
        if ($user->last_login_at && $user->last_login_at->diffInSeconds(now()) < 60) {
            return;
        }

        $user->increment('login_count');
        $user->last_login_at = now();
        $user->saveQuietly();
    }
}