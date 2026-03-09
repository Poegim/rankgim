<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RatingSnapshot extends Model
{
    protected $fillable = [
        'player_id',
        'rating',
        'rank',
        'games_played',
        'wins',
        'losses',
        'draws',
        'snapshot_date',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}
