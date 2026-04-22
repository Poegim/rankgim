<?php

namespace App\Livewire;

use Livewire\Attributes\Computed;
use Livewire\Component;

class Dashboard extends Component
{
    public bool $showMore = false;

    #[Computed]
    public function upcomingEvents()
    {
        return \App\Models\Event::where('starts_at', '>=', now()->subHours(\App\Models\Event::LIVE_WINDOW_HOURS))
            ->with('players')
            ->orderBy('starts_at')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}