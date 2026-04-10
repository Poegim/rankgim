<?php

namespace App\Livewire\Dashboard;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class SpreadChart extends Component
{
    #[Computed]
    public function data()
    {
        // Cache for 30 minutes — only changes when a new snapshot is taken
        return Cache::remember('dashboard.spread_chart', 3600, function () {
            $rows = DB::table('rating_snapshots as s')
                ->joinSub(
                    DB::table('rating_snapshots')
                        ->selectRaw('snapshot_date, MAX(`rank`) as max_rank')
                        ->groupBy('snapshot_date'),
                    'mx',
                    'mx.snapshot_date', '=', 's.snapshot_date'
                )
                ->selectRaw("
                    s.snapshot_date,
                    AVG(CASE WHEN s.rank <= 15 THEN s.rating END) as top_avg,
                    AVG(CASE WHEN s.rank > mx.max_rank - 15 THEN s.rating END) as bot_avg
                ")
                ->groupBy('s.snapshot_date')
                ->orderBy('s.snapshot_date')
                ->get();

            return $rows->filter(fn($r) => $r->top_avg && $r->bot_avg)
                ->map(fn($r) => [
                    'date'    => $r->snapshot_date,
                    'top_avg' => round($r->top_avg),
                    'bot_avg' => round($r->bot_avg),
                ]);
        });
    }

    public function render()
    {
        return view('livewire.dashboard.spread-chart');
    }
}