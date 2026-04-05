<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemStat extends Model
{
    protected $guarded = [];
    protected $primaryKey = 'key';
    public $incrementing  = false;
    protected $keyType    = 'string';

    protected $casts = [
        'value' => 'array',
    ];

    /**
     * Get a value by key, returns null if not found.
     */
    public static function get(string $key): mixed
    {
        return static::find($key)?->value;
    }

    /**
     * Set a value by key, creates or updates.
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}