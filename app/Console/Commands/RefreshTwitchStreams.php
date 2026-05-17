<?php

namespace App\Console\Commands;

use App\Services\Twitch\TwitchLiveStatusService;
use Illuminate\Console\Command;

/**
 * Triggers a Twitch API fetch and refreshes the cached live-streams snapshot.
 * Scheduled every 5 minutes alongside the SOOP refresh.
 */
class RefreshTwitchStreams extends Command
{
    protected $signature   = 'twitch:refresh-streams';
    protected $description = 'Refresh the cached snapshot of live Twitch StarCraft: Brood War streams.';

    public function handle(TwitchLiveStatusService $service): int
    {
        $this->info('Refreshing Twitch live streams...');
        $service->refresh();

        $fetchedAt = $service->lastFetchedAt();
        $this->info(
            $fetchedAt
                ? "Done. Snapshot timestamp: {$fetchedAt->toIso8601String()}"
                : 'Refresh completed but no snapshot was written (likely an API error — check logs).'
        );

        return self::SUCCESS;
    }
}