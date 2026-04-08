<?php

namespace App\View\Components;

use Illuminate\View\Component;

class AchievementCard extends Component
{
    // Tier visual styles — single source of truth, used in both player profile and browser
    public const TIER_STYLES = [
        's' => [
            'card'  => 'background: linear-gradient(145deg, #3d2200 0%, #1e1000 70%, #0a0500 100%);',
            'bar'   => 'background: linear-gradient(90deg, #854F0B, #FFD700, #EF9F27, #FFD700, #854F0B);',
            'tier'  => 'background: #EF9F27; color: #1e1000;',
            'date'  => 'color: #BA7517;',
            'cat'   => 'color: #EF9F27;',
            'name'  => 'color: #FFD700;',
            'desc'  => 'color: #EF9F27;',
            'label' => 'S',
        ],
        'a' => [
            'card'  => 'background: linear-gradient(145deg, #2e1000 0%, #180800 70%, #080300 100%);',
            'bar'   => 'background: linear-gradient(90deg, #712B13, #F0997B, #D85A30, #F0997B, #712B13);',
            'tier'  => 'background: #D85A30; color: #180800;',
            'date'  => 'color: #993C1D;',
            'cat'   => 'color: #F0997B;',
            'name'  => 'color: #FF8C55;',
            'desc'  => 'color: #D85A30;',
            'label' => 'A',
        ],
        'b' => [
            'card'  => 'background: linear-gradient(145deg, #231a4a 0%, #130e2a 70%, #080610 100%);',
            'bar'   => 'background: linear-gradient(90deg, #3C3489, #CECBF6, #7F77DD, #CECBF6, #3C3489);',
            'tier'  => 'background: #7F77DD; color: #130e2a;',
            'date'  => 'color: #534AB7;',
            'cat'   => 'color: #CECBF6;',
            'name'  => 'color: #E8E4FF;',
            'desc'  => 'color: #AFA9EC;',
            'label' => 'B',
        ],
        'c' => [
            'card'  => 'background: linear-gradient(145deg, #042240 0%, #021525 70%, #010810 100%);',
            'bar'   => 'background: linear-gradient(90deg, #0C447C, #B5D4F4, #378ADD, #B5D4F4, #0C447C);',
            'tier'  => 'background: #378ADD; color: #021525;',
            'date'  => 'color: #185FA5;',
            'cat'   => 'color: #B5D4F4;',
            'name'  => 'color: #D4EAFF;',
            'desc'  => 'color: #85B7EB;',
            'label' => 'C',
        ],
        'd' => [
            'card'  => 'background: linear-gradient(145deg, #122808 0%, #091803 70%, #040c02 100%);',
            'bar'   => 'background: linear-gradient(90deg, #27500A, #C0DD97, #639922, #C0DD97, #27500A);',
            'tier'  => 'background: #639922; color: #091803;',
            'date'  => 'color: #3B6D11;',
            'cat'   => 'color: #C0DD97;',
            'name'  => 'color: #D8F0A0;',
            'desc'  => 'color: #97C459;',
            'label' => 'D',
        ],
    ];

    // Category border colors
    public const CATEGORY_BORDERS = [
        'games'     => '#e24b4a',
        'activity'  => '#378ADD',
        'ranking'   => '#EF9F27',
        'rating'    => '#1D9E75',
        'streaks'   => '#D85A30',
        'rivalry'   => '#7F77DD',
        'community' => '#D4537E',
        'history'   => '#888780',
        'drama'     => '#F0997B',
        'calendar'  => '#85B7EB',
        'precision' => '#97C459',
        'prestige'  => '#FAC775',
        'secret'    => '#534AB7',
    ];

    // Category display labels with emoji
    public const CATEGORY_LABELS = [
        'games'     => '🎮 Games',
        'activity'  => '📅 Activity',
        'ranking'   => '🏆 Ranking',
        'rating'    => '📈 Rating',
        'streaks'   => '🔥 Streaks',
        'rivalry'   => '⚔️ Rivalry',
        'community' => '🌍 Community',
        'history'   => '🕰️ History',
        'drama'     => '🎭 Drama',
        'calendar'  => '📆 Calendar',
        'precision' => '🎯 Precision',
        'prestige'  => '💀 Prestige',
        'secret'    => '🔒 Secret',
    ];

    public array  $tierStyle;
    public string $borderColor;
    public string $categoryLabel;

    /**
     * @param  array       $achievement    Achievement data array (key, name, description, tier, category, owners_count, pct, masked)
     * @param  string|null $unlockedAt     Date string — shown on player profile cards
     * @param  int         $totalPlayers   Total ranked players — used to show pct
     * @param  bool        $showHoldersBtn Whether to render the "who?" button (browser admin view)
     */
    public function __construct(
        public array   $achievement,
        public ?string $unlockedAt     = null,
        public int     $totalPlayers   = 0,
        public bool    $showHoldersBtn = false,
    ) {
        $tier   = $achievement['tier']     ?? 'd';
        $cat    = $achievement['category'] ?? '';
        $masked = $achievement['masked']   ?? false;

        $this->tierStyle     = self::TIER_STYLES[$tier] ?? self::TIER_STYLES['d'];
        $this->borderColor   = $masked
            ? '#534AB7'
            : (self::CATEGORY_BORDERS[$cat] ?? '#52525b');
        $this->categoryLabel = self::CATEGORY_LABELS[$cat] ?? $cat;
    }

    public function render()
    {
        return view('components.achievement-card');
    }
}