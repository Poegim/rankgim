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

// Live stream cache refresh — SOOP and Twitch each have a thin API client.
// Both refresh every 5 minutes; well below any rate limit (~288 requests/day per platform).
// withoutOverlapping prevents pile-ups if a tick is slow; runInBackground keeps the
// scheduler loop non-blocking.
Schedule::command('soop:refresh-streams')
    ->everyFiveMinutes()
    ->withoutOverlapping(10)
    ->runInBackground();

Schedule::command('twitch:refresh-streams')
    ->everyMinute()
    ->withoutOverlapping(2)
    ->runInBackground();