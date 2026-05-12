<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SoopStreamer extends Model
{
    protected $fillable = ['user_id', 'label', 'race'];

    // Allowed race values matching the enum constraint on the DB column.
    // Null is also valid for streamers without a race (casters, official channels, teams).
    public const RACES = ['zerg', 'protoss', 'terran', 'random'];
}