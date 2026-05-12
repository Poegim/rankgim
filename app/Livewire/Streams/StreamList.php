<?php

namespace App\Livewire\Streams;

use App\Services\Soop\SoopLiveStatusService;
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

    #[Computed]
    public function streams(): array
    {
        return app(SoopLiveStatusService::class)->whitelistedLiveStreams();
    }

    #[Computed]
    public function lastFetchedAt(): ?\Illuminate\Support\Carbon
    {
        return app(SoopLiveStatusService::class)->lastFetchedAt();
    }

    #[Computed]
    public function isStale(): bool
    {
        return app(SoopLiveStatusService::class)->isStale();
    }

    public function render()
    {
        return view('livewire.streams.stream-list');
    }
}