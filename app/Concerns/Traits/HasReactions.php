<?php

namespace App\Concerns\Traits;

use App\Models\Reaction;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasReactions
{
    // ── Relationships ─────────────────────────────────

    public function reactions(): MorphMany
    {
        return $this->morphMany(Reaction::class, 'reactable');
    }

    // ── Helpers ───────────────────────────────────────

    public function reactionCounts(): array
    {
        return $this->reactions()
            ->selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();
    }

    public function userReaction(?int $userId): ?string
    {
        if (!$userId) {
            return null;
        }

        return $this->reactions()
            ->where('user_id', $userId)
            ->value('type');
    }

    public function toggleReaction(int $userId, string $type): void
    {
        $existing = $this->reactions()
            ->where('user_id', $userId)
            ->where('type', $type)
            ->first();

        if ($existing) {
            $existing->delete();
            return;
        }

        // Remove any other reaction from this user first
        $this->reactions()
            ->where('user_id', $userId)
            ->delete();

        $this->reactions()->create([
            'user_id' => $userId,
            'type'    => $type,
        ]);
    }
}