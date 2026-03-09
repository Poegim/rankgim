<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Player extends Model
{
    protected $fillable = [
        'name',
        'country',
        'country_code',
        'race',
        'player_id',
    ];

    public function aka(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id');
    }

    public function aliases(): HasMany
    {
        return $this->hasMany(Player::class, 'player_id');
    }

    public function rating(): HasOne
    {
        return $this->hasOne(PlayerRating::class);
    }

    public function ratingHistory(): HasMany
    {
        return $this->hasMany(RatingHistory::class);
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(RatingSnapshot::class);
    }
}
