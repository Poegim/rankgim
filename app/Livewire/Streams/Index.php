<?php

namespace App\Livewire\Streams;

use App\Services\Soop\SoopLiveStatusService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    /**
     * Cache re-read interval. The actual SOOP fetch runs on a 5-min schedule.
     */
    public int $pollSeconds = 60;

    /**
     * Race filter, persisted in URL so the view is shareable.
     * Empty string = no filter (show all races including null).
     */
    #[Url(as: 'race', except: '')]
    public string $raceFilter = '';

    /**
     * Available race filter tabs. Empty string = "All".
     */
    public array $raceTabs = ['', 'terran', 'protoss', 'zerg', 'random'];

    public function setRace(string $race): void
    {
        $this->raceFilter = in_array($race, $this->raceTabs, true) ? $race : '';
    }

    /**
     * Featured streams (whitelist), optionally filtered by race.
     */
    #[Computed]
    public function featured(): array
    {
        $streams = app(SoopLiveStatusService::class)->whitelistedLiveStreams();

        return $this->applyRaceFilter($streams);
    }

    /**
     * Non-whitelisted live streams. Race filter applied too —
     * but non-whitelisted streams have no race in our DB, so applying a
     * non-empty filter will always return an empty list. That's intentional:
     * a user filtering "Terran" doesn't want random unfiltered BJs.
     */
    #[Computed]
    public function others(): array
    {
        $streams = app(SoopLiveStatusService::class)->otherLiveStreams();

        return $this->applyRaceFilter($streams);
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

    /**
     * Filter a stream list by the currently selected race.
     * Empty filter passes everything through (incl. streams with null race).
     *
     * @param  array<int, array<string, mixed>> $streams
     * @return array<int, array<string, mixed>>
     */
    protected function applyRaceFilter(array $streams): array
    {
        if ($this->raceFilter === '') {
            return $streams;
        }

        return array_values(array_filter(
            $streams,
            fn ($s) => ($s['race'] ?? null) === $this->raceFilter,
        ));
    }

    public function render()
    {
        return view('livewire.streams.index');
    }
}