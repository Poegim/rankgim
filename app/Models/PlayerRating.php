<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerRating extends Model
{
    protected $fillable = [
        'player_id',
        'rating',
        'games_played',
        'wins',
        'losses',
        'draws',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}
