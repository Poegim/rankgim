<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\RatingHistory;

class HighestPeaks extends Component
{
    public string $region = '';

    #[Computed]
    public function peaks()
    {
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
    }

    public function render()
    {
        return view('livewire.highest-peaks');
    }
}