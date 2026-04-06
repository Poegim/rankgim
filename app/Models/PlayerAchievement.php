<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerAchievement extends Model
{
    protected $fillable = [
        'player_id',
        'key',
        'tier',
        'value',
        'unlocked_at',
    ];

    protected $casts = [
        'unlocked_at' => 'date',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}