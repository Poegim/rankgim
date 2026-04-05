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