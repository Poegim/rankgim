<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\SendEventReminderJob;
use App\Models\Event;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');



Schedule::call(function () {
    Event::where('starts_at', '<=', now()->addMinutes(30))
        ->where('starts_at', '>=', now())
        ->whereNull('reminder_sent_at')
        ->each(fn($event) => SendEventReminderJob::dispatch($event));
})->everyMinute();

// SOOP live stream cache refresh — single request to broad/list every 5 min,
// well below any sensible rate limit (288 requests/day).
// withoutOverlapping prevents pile-ups if SOOP is slow on a given tick.
Schedule::command('soop:refresh-streams')
    ->everyFiveMinutes()
    ->withoutOverlapping(10)
    ->runInBackground();