<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HeadToHead extends Model
{
    protected $table = 'head_to_head';
    
    public function playerA(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_a_id');
    }

    public function playerB(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_b_id');
    }

    /**
     * Get wins for a specific player in this matchup.
     */
    public function winsFor(int $playerId): int
    {
        if ($playerId === $this->player_a_id) {
            return $this->player_a_wins;
        }

        return $this->games_count - $this->player_a_wins;
    }

    /**
     * Find H2H record between two players regardless of which is player_a/player_b.
     */
    public static function between(int $id1, int $id2): ?self
    {
        return self::where('player_a_id', min($id1, $id2))
            ->where('player_b_id', max($id1, $id2))
            ->first();
    }
}