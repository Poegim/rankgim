<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    protected $fillable = ['tournament_id', 'winner_id', 'loser_id', 'date_time', 'result', 'user_id'];

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'winner_id');
    }

    public function loser(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'loser_id');
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function ratingHistory(): HasMany
    {
        return $this->hasMany(RatingHistory::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
