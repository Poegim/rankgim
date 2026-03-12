<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerName extends Model
{
    public $timestamps = false;

    protected $fillable = ['player_id', 'name', 'is_primary'];

    public function player()
    {
        return $this->belongsTo(Player::class);
    }
}