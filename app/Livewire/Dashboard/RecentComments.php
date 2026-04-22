<?php

namespace App\Livewire\Dashboard;

use App\Models\Comment;
use Livewire\Attributes\Computed;
use Livewire\Component;

class RecentComments extends Component
{
    #[Computed]
    public function comments()
    {
        return Comment::with(['user', 'commentable'])
            ->latest()
            ->limit(8)
            ->get()
            // Filter out orphaned comments (soft-deleted or missing parent model)
            ->filter(fn($c) => $c->commentable !== null)
            ->values();
    }

    /**
     * Build a human-readable label for the commentable model.
     */
    public function labelFor(Comment $comment): string
    {
        $model = $comment->commentable;

        return match (true) {
            $model instanceof \App\Models\Event  => $model->name,
            $model instanceof \App\Models\Player => $model->name,
            default                              => class_basename($model) . ' #' . $model->getKey(),
        };
    }

    /**
     * Build the URL to the commentable model (if linkable).
     */
    public function urlFor(Comment $comment): ?string
    {
        $model = $comment->commentable;

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
        return view('livewire.dashboard.recent-comments');
    }
}