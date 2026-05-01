<?php

namespace App\Models;

use App\Models\Article;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecalculationReport extends Model
{
    protected $fillable = [
        'recalculated_at',
        'previous_recalculated_at',
        'summary',
    ];

    protected $casts = [
        'recalculated_at'          => 'datetime',
        'previous_recalculated_at' => 'datetime',
        'summary'                  => 'array',
    ];

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }


}