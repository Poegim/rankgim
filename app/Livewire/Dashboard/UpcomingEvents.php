<?php

namespace App\Livewire\Dashboard;

use App\Models\Event;
use Livewire\Attributes\Computed;
use Livewire\Component;

class UpcomingEvents extends Component
{
    // How many events to show on the dashboard. Anything beyond this lives on /events.
    public const LIMIT = 3;

    #[Computed]
    public function events()
    {
        return Event::where('starts_at', '>=', now()->subHours(Event::LIVE_WINDOW_HOURS))
            ->with('players')
            ->orderBy('starts_at')
            ->limit(self::LIMIT)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard.upcoming-events');
    }
}