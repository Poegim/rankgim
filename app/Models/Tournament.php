<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tournament extends Model
{
    protected $fillable = [
        'name',
    ];

    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }
}
