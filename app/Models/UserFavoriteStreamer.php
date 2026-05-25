<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Personal favorite — a logged-in user has flagged a streamer (on any platform)
 * as one they want pinned to the top of the streams list.
 *
 * Independent of the admin-curated `streamers` whitelist:
 *   - Favorited whitelisted streamer → pinned to top of Featured
 *   - Favorited non-whitelisted streamer → promoted INTO Featured (top)
 *
 * Identifier mirrors `streamers.user_id` shape:
 *   - SOOP   → numeric/alphanumeric account user_id
 *   - Twitch → lowercase channel login
 */
class UserFavoriteStreamer extends Model
{
    protected $fillable = [
        'user_id',
        'platform',
        'streamer_user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}