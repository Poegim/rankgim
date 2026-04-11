<?php

namespace App\Livewire\Reactions;

use App\Models\Reaction;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ReactionBar extends Component
{
    #[Locked]
    public Model $model;

    public ?string $userReaction = null;
    public array $counts = [];

    public function mount(Model $model): void
    {
        $this->model = $model;
        $this->loadReactions();
    }

    public function toggleReaction(string $type): void
    {
        if (!auth()->check()) {
            return;
        }

        // Validate type against config
        if (!array_key_exists($type, config('reactions'))) {
            return;
        }

        $this->model->toggleReaction(auth()->id(), $type);
        $this->loadReactions();
    }

    private function loadReactions(): void
    {
        $this->counts = $this->model->reactionCounts();
        $this->userReaction = $this->model->userReaction(auth()->id());
    }

    public function render()
    {
        return view('livewire.reactions.reaction-bar', [
            'types' => config('reactions'),
        ]);
    }
}