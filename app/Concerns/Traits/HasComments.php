<?php

namespace App\Concerns\Traits;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasComments
{
    // ── Relationships ─────────────────────────────────

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    // ── Helpers ───────────────────────────────────────

    public function topLevelComments(): MorphMany
    {
        return $this->comments()
            ->whereNull('parent_id')
            ->with(['user', 'replies.user'])
            ->latest();
    }

    public function commentCount(): int
    {
        return $this->comments()->whereNull('parent_id')->count();
    }
}