<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RatingHistory extends Model
{
    protected $fillable = [
        'player_id',
        'game_id',
        'rating_before',
        'rating_after',
        'rating_change',
        'result',
        'played_at',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
