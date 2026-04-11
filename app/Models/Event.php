<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Event extends Model
{
    protected $fillable = [
        'name',
        'type',
        'description',
        'starts_at',
        'timezone',
        'created_by',
        'links',
        'is_online',
        'location',
        'reminder_sent_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
        'links' => 'array',
        'is_online' => 'boolean',
    ];

    /**
     * Supported link types with display info.
     */
    public const LINK_TYPES = [
        'twitch' => ['label' => 'Twitch', 'color' => '#9146ff'],
        'challonge' => ['label' => 'Challonge', 'color' => '#ff7324'],
        'youtube' => ['label' => 'YouTube', 'color' => '#ff0000'],
        'facebook' => ['label' => 'Facebook', 'color' => '#1877f2'],
        'discord' => ['label' => 'Discord', 'color' => '#5865f2'],
        'forum' => ['label' => 'Forum', 'color' => '#6b7280'],
        'other' => ['label' => 'Link', 'color' => '#6b7280'],
    ];

    public const TYPES = [
        'stream' => [
            'label'       => 'Stream / Watch',
            'badge'       => 'Stream',
            'color'       => '#9146ff',
            'description' => 'Live stream or tournament to watch',
        ],
        'open' => [
            'label'       => 'Open Tournament',
            'badge'       => 'Open',
            'color'       => '#f59e0b',
            'description' => 'Open tournament — register and play',
        ],
    ];

    

    /**
     * Common timezones for the picker — SC:BW community relevant.
     */
    public const TIMEZONES = [
        'UTC' => 'UTC',
        'Europe/London' => 'GMT / London',
        'Europe/Berlin' => 'CET / Berlin',
        'Europe/Warsaw' => 'CET / Warsaw',
        'Europe/Moscow' => 'MSK / Moscow',
        'Asia/Seoul' => 'KST / Seoul',
        'Asia/Tokyo' => 'JST / Tokyo',
        'Asia/Shanghai' => 'CST / Shanghai',
        'America/New_York' => 'EST / New York',
        'America/Chicago' => 'CST / Chicago',
        'America/Los_Angeles' => 'PST / Los Angeles',
        'America/Sao_Paulo' => 'BRT / São Paulo',
        'Australia/Sydney' => 'AEST / Sydney',
    ];

    // ── Relationships ─────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Players participating in or featured at this event.
     */
    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'event_player');
    }

    // ── Helpers ────────────────────────────────────────

    public function isPast(): bool
    {
        return $this->starts_at->isPast();
    }

    public function isUpcoming(): bool
    {
        return $this->starts_at->isFuture();
    }

    public function isStream(): bool
    {
        return $this->type === 'stream';
    }

    public function isOpen(): bool
    {
        return $this->type === 'open';
    }

    /**
     * Registration is open for open events until they start.
     */
    public function isRegistrationOpen(): bool
    {
        return $this->isOpen() && $this->isUpcoming();
    }

    /**
     * Key timezones always shown on the event card.
     */
    public const DISPLAY_TIMEZONES = [
        'Europe/Berlin' => 'CET',
        'America/New_York' => 'NY',
        'America/Los_Angeles' => 'LA',
        'Asia/Seoul' => 'KR',
    ];


    /**
     * Return dates + times in multiple timezones
     */
    public function displayDates(): array
    {
    $dates = [];
    foreach (self::DISPLAY_TIMEZONES as $tz => $label) {  // ← use constant
        $dates[] = [
            'label' => $label,
            'datetime' => $this->starts_at->copy()->setTimezone($tz)->format('D, M j H:i'),
        ];
    }

        return $dates;
    }

    public function startsAtLocal(): CarbonInterface
    {
        return $this->starts_at->copy()->setTimezone($this->timezone);
    }

    /**
     * Get starts_at in CET timezone
     */
    public function startsAtCET(): CarbonInterface
    {
        return $this->starts_at->copy()->setTimezone('Europe/Berlin');
    }


    public function hasLinks(): bool
    {
        return !empty($this->links);
    }

    /**
     * Get parsed links with type metadata merged in.
     */
    public function parsedLinks(): array
    {
        if (!$this->hasLinks()) {
            return [];
        }

        return collect($this->links)->map(function ($link) {
            $type = $link['type'] ?? 'other';
            $meta = self::LINK_TYPES[$type] ?? self::LINK_TYPES['other'];

            return array_merge($meta, $link); // ← link nadpisuje meta
        })->toArray();
    }
}