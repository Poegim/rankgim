<?php

namespace App\Livewire;

use App\Models\RatingHistory;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Component;


class HighestPeaks extends Component
{
    public string $region = '';

    #[Computed]
    public function peaks()
    {
        return Cache::remember('dashboard.highestPeaks.' . ($this->region ?: 'all'), 3600, function () {
            return RatingHistory::selectRaw('player_id, MAX(rating_after) as peak_rating')
                ->when($this->region, function ($query) {
                    $codes = collect(config('countries'))->where('region', $this->region)->pluck('code')->toArray();
                    $query->whereHas('player', fn($q) => $q->whereIn('country_code', $codes));
                })
                ->groupBy('player_id')
                ->orderByDesc('peak_rating')
                ->with('player')
                ->limit(10)
                ->get();
        });
    }

    public function render()
    {
        return view('livewire.highest-peaks');
    }
}