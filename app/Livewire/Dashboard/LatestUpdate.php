<?php

namespace App\Livewire\Dashboard;

use App\Models\Article;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class LatestUpdate extends Component
{
    /**
     * Show the most recent N published articles regardless of type.
     * Type is rendered as a badge so users can distinguish auto updates from manual news.
     */
    public int $limit = 5;

    #[Computed]
    public function articles(): Collection
    {
        return Article::query()
            ->published()
            ->withCount(['comments' => fn($q) => $q->whereNull('parent_id')])
            ->orderByDesc('published_at')
            ->limit($this->limit)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard.latest-update');
    }
}