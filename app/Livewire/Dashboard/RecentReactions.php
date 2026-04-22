<?php

namespace App\Livewire\Dashboard;

use App\Models\Reaction;
use Livewire\Attributes\Computed;
use Livewire\Component;

class RecentReactions extends Component
{
    #[Computed]
    public function reactions()
    {
        return Reaction::with(['user', 'reactable'])
            ->latest()
            ->limit(8)
            ->get()
            // Filter out reactions whose related model was deleted
            ->filter(fn($r) => $r->reactable !== null)
            ->values();
    }

    /**
     * Build a human-readable label for the reactable model.
     * Returns the model name + its identifier (event name, player name, etc.)
     */
    public function labelFor(Reaction $reaction): string
    {
        $model = $reaction->reactable;

        return match (true) {
            $model instanceof \App\Models\Event  => $model->name,
            $model instanceof \App\Models\Player => $model->name,
            default                              => class_basename($model) . ' #' . $model->getKey(),
        };
    }

    /**
     * Build the URL to the reactable model (if linkable).
     */
    public function urlFor(Reaction $reaction): ?string
    {
        $model = $reaction->reactable;

        return match (true) {
            $model instanceof \App\Models\Event  => route('events.index'),
            $model instanceof \App\Models\Player => route('players.show', [
                'id'   => $model->id,
                'slug' => \Illuminate\Support\Str::slug($model->name),
            ]),
            default => null,
        };
    }

    public function render()
    {
        return view('livewire.dashboard.recent-reactions');
    }
}