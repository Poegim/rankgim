<?php

namespace App\Livewire\Streams;

use App\Services\Streams\LiveStreamsService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class StreamList extends Component
{
    /**
     * Polling interval in seconds. The cache itself is refreshed by the
     * scheduler every 5 minutes; this poll just re-reads the cache so users
     * see new streams without a full page refresh.
     */
    public int $pollSeconds = 60;

    /**
     * When true, render the "Other live streams" section under featured ones.
     * Disabled on the dashboard widget (featured only), enabled on /streams page.
     */
    public bool $showOthers = false;

    #[Computed]
    public function streams(): array
    {
        // Dashboard widget always merges both platforms — platform filtering
        // is a /streams-page concept only.
        return app(LiveStreamsService::class)->featuredStreams(null, auth()->user());
    }

    #[Computed]
    public function others(): array
    {
        if (! $this->showOthers) {
            return [];
        }

        return app(LiveStreamsService::class)->otherStreams(null, auth()->user());
    }

    #[Computed]
    public function lastFetchedAt(): ?\Illuminate\Support\Carbon
    {
        return app(LiveStreamsService::class)->lastFetchedAt();
    }

    #[Computed]
    public function isStale(): bool
    {
        return app(LiveStreamsService::class)->isStale();
    }

    public function render()
    {
        return view('livewire.streams.stream-list');
    }
}