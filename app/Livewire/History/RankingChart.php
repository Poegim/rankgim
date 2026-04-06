<?php

namespace App\Livewire\History;

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\RatingSnapshot;
use App\Models\Player;
use Illuminate\Support\Facades\DB;

class RankingChart extends Component
{
#[Computed]
public function chartSeries(): array
{
    // Find all players who ever held a position in top 10
    $playerIds = DB::table('rating_snapshots')
        ->where('rank', '<=', 5)
        ->select('player_id')
        ->distinct()
        ->pluck('player_id');

    // Fetch player details — main players only, no aliases
    $players = Player::whereIn('id', $playerIds)
        ->whereNull('player_id')
        ->get(['id', 'name', 'race'])
        ->keyBy('id');

    // Only keep IDs that exist as main players
    $playerIds = $players->keys();

    // Fetch all snapshots for these players ordered by date
    $snapshots = RatingSnapshot::whereIn('player_id', $playerIds)
        ->orderBy('snapshot_date')
        ->get(['player_id', 'rank', 'rating', 'snapshot_date']);

    // Build one series per player for ApexCharts
    $series = [];
    foreach ($playerIds as $playerId) {
        $player = $players[$playerId];

        $data = $snapshots
            ->where('player_id', $playerId)
            ->map(fn($s) => [
                'x'      => $s->snapshot_date,
                'y'      => $s->rank,
                'rating' => $s->rating,
            ])
            ->values();

        if ($data->isEmpty()) continue;

        $series[] = [
            'name' => $player->name,
            'race' => $player->race,
            'data' => $data,
        ];
    }

    return $series;
}

    public function render()
    {
        return view('livewire.history.ranking-chart');
    }
}