<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Achievements definitions
    |--------------------------------------------------------------------------
    |
    | key         — unique identifier, used in player_achievements table
    | name        — display name
    | description — shown on player profile
    | tier        — d, c, b, a, s (D = easiest, S = hardest)
    | category    — used for grouping on profile page
    | secret      — if true, name/description hidden until unlocked
    | group       — progressive achievements in same group show only highest tier
    |               null = always show (standalone achievement)
    | lore        — optional explanation of the cultural/historical reference behind the name
    |               null = no reference to explain
    |
    | Tier colors (StarCraft League Ranks):
    |   D — green   #44BB44
    |   C — blue    #4488FF
    |   B — purple  #CC44CC
    |   A — orange  #FF8C00
    |   S — yellow  #FFD700
    |
    */

    // -------------------------------------------------------------------------
    // Games played
    // -------------------------------------------------------------------------
    'showing_up' => [
        'name'        => 'Showing Up',
        'description' => '15 games. You showed up. That\'s the hardest part.',
        'tier'        => 'd',
        'category'    => 'games',
        'secret'      => false,
        'group'       => 'games_played',
        'lore'        => null,
    ],
    'rocky' => [
        'name'        => 'Rocky',
        'description' => '75 games. Nobody hits as hard as life.',
        'tier'        => 'c',
        'category'    => 'games',
        'secret'      => false,
        'group'       => 'games_played',
        'lore'        => 'Rocky (1976). Mickey tells Rocky that nobody hits as hard as life — what matters is that you keep getting up.',
    ],
    'inception' => [
        'name'        => 'Inception',
        'description' => '250 games. How deep does the rabbit hole go?',
        'tier'        => 'b',
        'category'    => 'games',
        'secret'      => false,
        'group'       => 'games_played',
        'lore'        => 'Nolan\'s Inception (2010) — dreams within dreams. The rabbit hole line nods to Alice in Wonderland. Both ask: how far down are you willing to go?',
    ],
    'there_is_no_spoon' => [
        'name'        => 'There Is No Spoon',
        'description' => '500 games. There is no free time either.',
        'tier'        => 'a',
        'category'    => 'games',
        'secret'      => false,
        'group'       => 'games_played',
        'lore'        => 'A nod to a famous scene in "The Matrix" (1999).',
    ],
    'tears_in_rain' => [
        'name'        => 'Tears in Rain',
        'description' => '1000 games. I\'ve seen things you wouldn\'t believe. Carriers over Aiur. Zerg swarms darkening the sky. All those moments, lost in time. Like tears in rain.',
        'tier'        => 's',
        'category'    => 'games',
        'secret'      => false,
        'group'       => 'games_played',
        'lore'        => 'Inspired by Roy Batty’s legendary dying monologue in "Blade Runner" (1982) — "I\'ve seen things you people wouldn\'t believe..."',
    ],

    // -------------------------------------------------------------------------
    // Activity — total months (not consecutive)
    // -------------------------------------------------------------------------
    'regular' => [
        'name'        => 'Regular',
        'description' => 'Be active for 12 months in total',
        'tier'        => 'd',
        'category'    => 'activity',
        'secret'      => false,
        'group'       => 'activity_total',
        'lore'        => null,
    ],
    'dedicated' => [
        'name'        => 'Dedicated',
        'description' => 'Be active for 24 months in total',
        'tier'        => 'c',
        'category'    => 'activity',
        'secret'      => false,
        'group'       => 'activity_total',
        'lore'        => null,
    ],
    'committed' => [
        'name'        => 'Committed',
        'description' => 'Be active for 36 months in total',
        'tier'        => 'b',
        'category'    => 'activity',
        'secret'      => false,
        'group'       => 'activity_total',
        'lore'        => null,
    ],
    'obsessed' => [
        'name'        => 'Obsessed',
        'description' => 'Be active for 48 months in total',
        'tier'        => 'a',
        'category'    => 'activity',
        'secret'      => false,
        'group'       => 'activity_total',
        'lore'        => null,
    ],
    'immortal' => [
        'name'        => 'Immortal',
        'description' => 'Be active for 60 months in total',
        'tier'        => 's',
        'category'    => 'activity',
        'secret'      => false,
        'group'       => 'activity_total',
        'lore'        => null,
    ],

    // -------------------------------------------------------------------------
    // Activity — consecutive months
    // -------------------------------------------------------------------------
    'on_a_roll' => [
        'name'        => 'On a Roll',
        'description' => 'Be active for 3 consecutive months',
        'tier'        => 'd',
        'category'    => 'activity',
        'secret'      => false,
        'group'       => 'activity_streak',
        'lore'        => null,
    ],
    'consistent' => [
        'name'        => 'Consistent',
        'description' => 'Be active for 6 consecutive months',
        'tier'        => 'c',
        'category'    => 'activity',
        'secret'      => false,
        'group'       => 'activity_streak',
        'lore'        => null,
    ],
    'unstoppable_activity' => [
        'name'        => 'Unstoppable',
        'description' => 'Be active for 12 consecutive months',
        'tier'        => 'b',
        'category'    => 'activity',
        'secret'      => false,
        'group'       => 'activity_streak',
        'lore'        => null,
    ],
    'iron_will' => [
        'name'        => 'Iron Will',
        'description' => 'Be active for 18 consecutive months',
        'tier'        => 'a',
        'category'    => 'activity',
        'secret'      => false,
        'group'       => 'activity_streak',
        'lore'        => null,
    ],
    'machine' => [
        'name'        => 'Machine',
        'description' => 'Be active for 24 consecutive months',
        'tier'        => 's',
        'category'    => 'activity',
        'secret'      => false,
        'group'       => 'activity_streak',
        'lore'        => null,
    ],

    // -------------------------------------------------------------------------
    // Ranking — peak position
    // -------------------------------------------------------------------------
    'ranked' => [
        'name'        => 'Ranked',
        'description' => 'Enter the ranking for the first time',
        'tier'        => 'd',
        'category'    => 'ranking',
        'secret'      => false,
        'group'       => 'top_rank',
        'lore'        => null,
    ],
    'top_100' => [
        'name'        => 'Top 100',
        'description' => 'Reach top 100',
        'tier'        => 'd',
        'category'    => 'ranking',
        'secret'      => false,
        'group'       => 'top_rank',
        'lore'        => null,
    ],
    'top_50' => [
        'name'        => 'Top 50',
        'description' => 'Reach top 50',
        'tier'        => 'c',
        'category'    => 'ranking',
        'secret'      => false,
        'group'       => 'top_rank',
        'lore'        => null,
    ],
    'top_25' => [
        'name'        => 'Top 25',
        'description' => 'Reach top 25',
        'tier'        => 'b',
        'category'    => 'ranking',
        'secret'      => false,
        'group'       => 'top_rank',
        'lore'        => null,
    ],
    'elite' => [
        'name'        => 'Elite',
        'description' => 'Reach top 10',
        'tier'        => 'a',
        'category'    => 'ranking',
        'secret'      => false,
        'group'       => 'top_rank',
        'lore'        => null,
    ],
    'podium' => [
        'name'        => 'Podium',
        'description' => 'Reach top 3',
        'tier'        => 'a',
        'category'    => 'ranking',
        'secret'      => false,
        'group'       => 'top_rank',
        'lore'        => null,
    ],
    'the_best' => [
        'name'        => 'The Best',
        'description' => 'Reach #1',
        'tier'        => 's',
        'category'    => 'ranking',
        'secret'      => false,
        'group'       => 'top_rank',
        'lore'        => null,
    ],

    // -------------------------------------------------------------------------
    // Ranking — time in top 10
    // -------------------------------------------------------------------------
    'fixture' => [
        'name'        => 'Fixture',
        'description' => 'Spend 3 months in top 10',
        'tier'        => 'c',
        'category'    => 'ranking',
        'secret'      => false,
        'group'       => 'top10_time',
        'lore'        => null,
    ],
    'pillar' => [
        'name'        => 'Pillar',
        'description' => 'Spend 6 months in top 10',
        'tier'        => 'b',
        'category'    => 'ranking',
        'secret'      => false,
        'group'       => 'top10_time',
        'lore'        => null,
    ],
    'institution' => [
        'name'        => 'Institution',
        'description' => 'Spend 12 months in top 10',
        'tier'        => 'a',
        'category'    => 'ranking',
        'secret'      => false,
        'group'       => 'top10_time',
        'lore'        => null,
    ],
    'monument' => [
        'name'        => 'Monument',
        'description' => 'Spend 24 months in top 10',
        'tier'        => 's',
        'category'    => 'ranking',
        'secret'      => false,
        'group'       => 'top10_time',
        'lore'        => null,
    ],

    // -------------------------------------------------------------------------
    // Ranking — returns after inactivity
    // -------------------------------------------------------------------------
    'kings_return' => [
        'name'        => "King's Return",
        'description' => 'Return to top 10 after 6+ months away',
        'tier'        => 'b',
        'category'    => 'ranking',
        'secret'      => false,
        'group'       => 'kings_return',
        'lore'        => null,
    ],
    'legends_return' => [
        'name'        => "Legend's Return",
        'description' => 'Return to top 10 after 12+ months away',
        'tier'        => 'a',
        'category'    => 'ranking',
        'secret'      => false,
        'group'       => 'kings_return',
        'lore'        => null,
    ],
    'ghosts_return' => [
        'name'        => "Ghost's Return",
        'description' => 'Return to top 10 after 24+ months away',
        'tier'        => 's',
        'category'    => 'ranking',
        'secret'      => false,
        'group'       => 'kings_return',
        'lore'        => null,
    ],

    // -------------------------------------------------------------------------
    // Rating milestones
    // -------------------------------------------------------------------------
    'rising' => [
        'name'        => 'Rising',
        'description' => 'Reach 1100 rating',
        'tier'        => 'd',
        'category'    => 'rating',
        'secret'      => false,
        'group'       => 'rating_milestone',
        'lore'        => null,
    ],
    'solid' => [
        'name'        => 'Solid',
        'description' => 'Reach 1200 rating',
        'tier'        => 'd',
        'category'    => 'rating',
        'secret'      => false,
        'group'       => 'rating_milestone',
        'lore'        => null,
    ],
    'strong' => [
        'name'        => 'Strong',
        'description' => 'Reach 1300 rating',
        'tier'        => 'c',
        'category'    => 'rating',
        'secret'      => false,
        'group'       => 'rating_milestone',
        'lore'        => null,
    ],
    'dangerous' => [
        'name'        => 'Dangerous',
        'description' => 'Reach 1400 rating',
        'tier'        => 'c',
        'category'    => 'rating',
        'secret'      => false,
        'group'       => 'rating_milestone',
        'lore'        => null,
    ],
    'fearsome' => [
        'name'        => 'Fearsome',
        'description' => 'Reach 1500 rating',
        'tier'        => 'b',
        'category'    => 'rating',
        'secret'      => false,
        'group'       => 'rating_milestone',
        'lore'        => null,
    ],
    'dominant' => [
        'name'        => 'Dominant',
        'description' => 'Reach 1600 rating',
        'tier'        => 'b',
        'category'    => 'rating',
        'secret'      => false,
        'group'       => 'rating_milestone',
        'lore'        => null,
    ],
    'elite_rating' => [
        'name'        => 'Elite',
        'description' => 'Reach 1700 rating',
        'tier'        => 'a',
        'category'    => 'rating',
        'secret'      => false,
        'group'       => 'rating_milestone',
        'lore'        => null,
    ],
    'terrifying' => [
        'name'        => 'Terrifying',
        'description' => 'Reach 1800 rating',
        'tier'        => 'a',
        'category'    => 'rating',
        'secret'      => false,
        'group'       => 'rating_milestone',
        'lore'        => null,
    ],
    'legendary' => [
        'name'        => 'Legendary',
        'description' => 'Reach 1900 rating',
        'tier'        => 's',
        'category'    => 'rating',
        'secret'      => false,
        'group'       => 'rating_milestone',
        'lore'        => null,
    ],
    'mythical' => [
        'name'        => 'Mythical',
        'description' => 'Reach 2000 rating',
        'tier'        => 's',
        'category'    => 'rating',
        'secret'      => false,
        'group'       => 'rating_milestone',
        'lore'        => null,
    ],
    'transcendent' => [
        'name'        => 'Transcendent',
        'description' => 'Reach 2100 rating',
        'tier'        => 's',
        'category'    => 'rating',
        'secret'      => false,
        'group'       => 'rating_milestone',
        'lore'        => null,
    ],
    'otherworldly' => [
        'name'        => 'Otherworldly',
        'description' => 'Reach 2200 rating',
        'tier'        => 's',
        'category'    => 'rating',
        'secret'      => false,
        'group'       => 'rating_milestone',
        'lore'        => null,
    ],
    'god_tier' => [
        'name'        => 'God-tier',
        'description' => 'Reach 2300+ rating',
        'tier'        => 's',
        'category'    => 'rating',
        'secret'      => false,
        'group'       => 'rating_milestone',
        'lore'        => null,
    ],
    'giant_slayer' => [
        'name'        => 'Giant Slayer',
        'description' => 'Beat a player rated 200+ higher than you (both players must have 30+ games)',
        'tier'        => 'a',
        'category'    => 'rating',
        'secret'      => false,
        'group'       => 'giant_slayer',
        'lore'        => null,
    ],
    'david_vs_goliath' => [
        'name'        => 'David vs Goliath',
        'description' => 'Beat a player rated 300+ higher than you (both players must have 30+ games)',
        'tier'        => 'a',
        'category'    => 'rating',
        'secret'      => false,
        'group'       => 'giant_slayer',
        'lore'        => null,
    ],
    'rocket' => [
        'name'        => 'Rocket',
        'description' => 'Gain 100+ rating points in a single month',
        'tier'        => 'b',
        'category'    => 'rating',
        'secret'      => false,
        'group'       => null,
        'lore'        => null,
    ],
    'glass_cannon' => [
        'name'        => 'Glass Cannon',
        'description' => 'Reach 1500+ rating with more losses than wins',
        'tier'        => 'a',
        'category'    => 'rating',
        'secret'      => false,
        'group'       => null,
        'lore'        => null,
    ],

    // -------------------------------------------------------------------------
    // Win streaks
    // -------------------------------------------------------------------------
    'hat_trick' => [
        'name'        => 'Hat Trick',
        'description' => 'Win 3 games in a row',
        'tier'        => 'd',
        'category'    => 'streaks',
        'secret'      => false,
        'group'       => 'win_streak',
        'lore'        => null,
    ],
    'hot_streak' => [
        'name'        => 'Hot Streak',
        'description' => 'Win 5 games in a row',
        'tier'        => 'c',
        'category'    => 'streaks',
        'secret'      => false,
        'group'       => 'win_streak',
        'lore'        => null,
    ],
    'rampage' => [
        'name'        => 'Rampage',
        'description' => 'Win 10 games in a row',
        'tier'        => 'b',
        'category'    => 'streaks',
        'secret'      => false,
        'group'       => 'win_streak',
        'lore'        => null,
    ],
    'unstoppable_streak' => [
        'name'        => 'Unstoppable',
        'description' => 'Win 15 games in a row',
        'tier'        => 'a',
        'category'    => 'streaks',
        'secret'      => false,
        'group'       => 'win_streak',
        'lore'        => null,
    ],
    'juggernaut' => [
        'name'        => 'Juggernaut',
        'description' => 'Win 25 games in a row',
        'tier'        => 's',
        'category'    => 'streaks',
        'secret'      => false,
        'group'       => 'win_streak',
        'lore'        => null,
    ],
    'terminator' => [
        'name'        => 'Terminator',
        'description' => 'Win 50 games in a row',
        'tier'        => 's',
        'category'    => 'streaks',
        'secret'      => false,
        'group'       => 'win_streak',
        'lore'        => null,
    ],
    'phoenix' => [
        'name'        => 'Phoenix',
        'description' => 'Return to playing after 12+ months away',
        'tier'        => 'c',
        'category'    => 'streaks',
        'secret'      => false,
        'group'       => null,
        'lore'        => 'The mythological bird that burns to ashes and is reborn. A symbol of resurrection across Egyptian, Greek, and Persian cultures.',
    ],

    // -------------------------------------------------------------------------
    // Rivalry
    // -------------------------------------------------------------------------
    'rival' => [
        'name'        => 'Rival',
        'description' => 'Lose to the same player 5 times',
        'tier'        => 'd',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'rivalry_losses',
        'lore'        => null,
    ],
    'cursed' => [
        'name'        => 'Cursed',
        'description' => 'Lose to the same player 10 times',
        'tier'        => 'c',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'rivalry_losses',
        'lore'        => null,
    ],
    'bully' => [
        'name'        => 'Bully',
        'description' => 'Beat the same player 10 times',
        'tier'        => 'c',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'rivalry_wins',
        'lore'        => null,
    ],
    'executioner' => [
        'name'        => 'Executioner',
        'description' => 'Beat the same player 20 times',
        'tier'        => 'b',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'rivalry_wins',
        'lore'        => null,
    ],
    'the_rematch' => [
        'name'        => 'The Rematch',
        'description' => 'Play the same player 30+ times',
        'tier'        => 'b',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'rivalry_total',
        'lore'        => null,
    ],
    'the_rivalry' => [
        'name'        => 'The Rivalry',
        'description' => 'Play the same player 50+ times',
        'tier'        => 'a',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'rivalry_total',
        'lore'        => null,
    ],
    'the_feud' => [
        'name'        => 'The Feud',
        'description' => 'Play the same player 100+ times',
        'tier'        => 's',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'rivalry_total',
        'lore'        => null,
    ],

    // -------------------------------------------------------------------------
    // Mirror Master
    // -------------------------------------------------------------------------
    'mirror_master_d' => [
        'name'        => 'Mirror Master',
        'description' => 'Win 5 games in a mirror matchup',
        'tier'        => 'd',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'mirror_master',
        'lore'        => null,
    ],
    'mirror_master_c' => [
        'name'        => 'Mirror Master',
        'description' => 'Win 10 games in a mirror matchup',
        'tier'        => 'c',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'mirror_master',
        'lore'        => null,
    ],
    'mirror_master_b' => [
        'name'        => 'Mirror Master',
        'description' => 'Win 25 games in a mirror matchup',
        'tier'        => 'b',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'mirror_master',
        'lore'        => null,
    ],
    'mirror_master_a' => [
        'name'        => 'Mirror Master',
        'description' => 'Win 50 games in a mirror matchup',
        'tier'        => 'a',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'mirror_master',
        'lore'        => null,
    ],
    'mirror_master_s' => [
        'name'        => 'Mirror Master',
        'description' => 'Win 100 games in a mirror matchup',
        'tier'        => 's',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'mirror_master',
        'lore'        => null,
    ],

    // -------------------------------------------------------------------------
    // Race Slayer — Protoss vs Zerg
    // -------------------------------------------------------------------------

    'protoss_slayer_zerg_d' => [
        'name'        => 'Psi Emitter',
        'description' => 'Win 5 games as Protoss against Zerg',
        'tier'        => 'd',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'protoss_slayer_zerg',
        'lore'        => 'A device that broadcast psionic signals to attract the Zerg. Mengsk used one on Tarsonis to lure the Swarm — and to betray Kerrigan.',
    ],

    'protoss_slayer_zerg_c' => [
        'name'        => 'Gantrithor Has Arrived',
        'description' => 'Win 10 games as Protoss against Zerg',
        'tier'        => 'c',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'protoss_slayer_zerg',
        'lore'        => null,
    ],

    'protoss_slayer_zerg_b' => [
        'name'        => 'We Do Not Negotiate With Insects',
        'description' => 'Win 25 games as Protoss against Zerg',
        'tier'        => 'b',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'protoss_slayer_zerg',
        'lore' => 'Reference to the iconic scene in Tropic Thunder (2008) where Tom Cruise as Les Grossman declares, "We don’t negotiate with terrorists."',
    ],

    'protoss_slayer_zerg_a' => [
        'name'        => 'This Is Aiur!',
        'description' => 'Win 50 games as Protoss against Zerg',
        'tier'        => 'a',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'protoss_slayer_zerg',
        'lore'        => '"This is Sparta!" from 300 (2006).',
    ],

    'protoss_slayer_zerg_s' => [
        'name'        => 'The Claws Are Silent',
        'description' => 'Win 100 games as Protoss against Zerg',
        'tier'        => 's',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'protoss_slayer_zerg',
        'lore'        => 'Riffs on MacArthur\'s words after Japan\'s surrender: "The guns are silent. A great tragedy has ended." Here, it\'s the Zerg claws that fall silent.',
    ],

    // -------------------------------------------------------------------------
    // Race Slayer — Protoss vs Terran
    // -------------------------------------------------------------------------

    'protoss_slayer_terran_d' => [
        'name'        => 'Ghost Buster',
        'description' => 'Win 5 games as Protoss against Terran',
        'tier'        => 'd',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'protoss_slayer_terran',
        'lore'        => null,
    ],

    'protoss_slayer_terran_c' => [
        'name'        => 'Bunker Breaker',
        'description' => 'Win 10 games as Protoss against Terran',
        'tier'        => 'c',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'protoss_slayer_terran',
        'lore'        => null,
    ],

    'protoss_slayer_terran_b' => [
        'name'        => 'I Find Your Lack Of Shields Disturbing',
        'description' => 'Win 25 games as Protoss against Terran',
        'tier'        => 'b',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'protoss_slayer_terran',
        'lore'        => 'Darth Vader\'s "I find your lack of faith disturbing" from Star Wars (1977).',
    ],

    'protoss_slayer_terran_a' => [
        'name'        => 'Kerrigan Had The Right Idea',
        'description' => 'Win 50 games as Protoss against Terran',
        'tier'        => 'a',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'protoss_slayer_terran',
        'lore'        => null,
    ],

    'protoss_slayer_terran_s' => [
        'name'        => "Mengsk's Nightmare",
        'description' => 'Win 100 games as Protoss against Terran',
        'tier'        => 's',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'protoss_slayer_terran',
        'lore'        => null,
    ],

    // -------------------------------------------------------------------------
    // Race Slayer — Zerg vs Protoss
    // -------------------------------------------------------------------------

    'zerg_slayer_protoss_d' => [
        'name'        => 'Nexus Eater',
        'description' => 'Win 5 games as Zerg against Protoss',
        'tier'        => 'd',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'zerg_slayer_protoss',
        'lore'        => null,
    ],

    'zerg_slayer_protoss_c' => [
        'name'        => 'No Carriers Allowed',
        'description' => 'Win 10 games as Zerg against Protoss',
        'tier'        => 'c',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'zerg_slayer_protoss',
        'lore'        => null,
    ],

    'zerg_slayer_protoss_b' => [
        'name'        => 'Khas Would Be Disappointed',
        'description' => 'Win 25 games as Zerg against Protoss',
        'tier'        => 'b',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'zerg_slayer_protoss',
        'lore'        => null,
    ],

    'zerg_slayer_protoss_a' => [
        'name'        => 'Your Shields Taste Like Chicken',
        'description' => 'Win 50 games as Zerg against Protoss',
        'tier'        => 'a',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'zerg_slayer_protoss',
        'lore'        => null,
    ],

    'zerg_slayer_protoss_s' => [
        'name'        => 'I am The Swarm.',
        'description' => 'Win 100 games as Zerg against Protoss',
        'tier'        => 's',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'zerg_slayer_protoss',
        'lore'        => null,
    ],

    // -------------------------------------------------------------------------
    // Race Slayer — Zerg vs Terran
    // -------------------------------------------------------------------------

    'zerg_slayer_terran_d' => [
        'name'        => 'Bio Hazard',
        'description' => 'Win 5 games as Zerg against Terran',
        'tier'        => 'd',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'zerg_slayer_terran',
        'lore'        => null,
    ],

    'zerg_slayer_terran_c' => [
        'name'        => 'The Planet Is Ours Now',
        'description' => 'Win 10 games as Zerg against Terran',
        'tier'        => 'c',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'zerg_slayer_terran',
        'lore'        => null,
    ],

    'zerg_slayer_terran_b' => [
        'name'        => 'You Asked For This, Mengsk',
        'description' => 'Win 25 games as Zerg against Terran',
        'tier'        => 'b',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'zerg_slayer_terran',
        'lore'        => null,
    ],

    'zerg_slayer_terran_a' => [
        'name'        => 'Sarah Sends Her Regards',
        'description' => 'Win 50 games as Zerg against Terran',
        'tier'        => 'a',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'zerg_slayer_terran',
        'lore'        => null,
    ],

    'zerg_slayer_terran_s' => [
        'name'        => 'Resistance Is Futile',
        'description' => 'Win 100 games as Zerg against Terran',
        'tier'        => 's',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'zerg_slayer_terran',
        'lore' => 'Inspired by the Borg from Star Trek: The Next Generation',
    ],

    // -------------------------------------------------------------------------
    // Race Slayer — Terran vs Zerg
    // -------------------------------------------------------------------------

    'terran_slayer_zerg_d' => [
        'name'        => 'Pest Control',
        'description' => 'Win 5 games as Terran against Zerg',
        'tier'        => 'd',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'terran_slayer_zerg',
        'lore'        => null,
    ],

    'terran_slayer_zerg_c' => [
        'name'        => 'The Only Good Bug Is A Dead Bug',
        'description' => 'Win 10 games as Terran against Zerg',
        'tier'        => 'c',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'terran_slayer_zerg',
        'lore'        => 'Starship Troopers (1997) — a propaganda slogan from the in-universe news broadcasts during humanity\'s war with Arachnids.',
    ],

    'terran_slayer_zerg_b' => [
        'name'        => 'I Love The Smell Of Napalm',
        'description' => 'Win 25 games as Terran against Zerg',
        'tier'        => 'b',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'terran_slayer_zerg',
        'lore'        => 'Lt. Col. Kilgore on a napalm-scorched beach in Apocalypse Now (1979). Completely unbothered by the chaos. One of cinema\'s most memorable lines.',
    ],

    'terran_slayer_zerg_a' => [
        'name'        => 'I Say We Nuke The Site From Orbit',
        'description' => 'Win 50 games as Terran against Zerg',
        'tier'        => 'a',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'terran_slayer_zerg',
        'lore'        => 'Ripley\'s solution to the Xenomorph problem in Aliens (1986): "Nuke the entire site from orbit. It\'s the only way to be sure."',
    ],

    'terran_slayer_zerg_s' => [
        'name'        => 'The Brood War',
        'description' => 'Win 100 games as Terran against Zerg',
        'tier'        => 's',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'terran_slayer_zerg',
        'lore'        => null,
    ],

    // -------------------------------------------------------------------------
    // Race Slayer — Terran vs Protoss
    // -------------------------------------------------------------------------

    'terran_slayer_protoss_d' => [
        'name'        => 'Shield Breaker',
        'description' => 'Win 5 games as Terran against Protoss',
        'tier'        => 'd',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'terran_slayer_protoss',
        'lore'        => null,
    ],

    'terran_slayer_protoss_c' => [
        'name'        => 'If It Bleeds We Can Kill It',
        'description' => 'Win 10 games as Terran against Protoss',
        'tier'        => 'c',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'terran_slayer_protoss',
        'lore'        => 'Dutch (Schwarzenegger) in Predator (1987), after discovering the alien hunter can be wounded. The moment when an unstoppable enemy becomes stoppable.',
    ],

    'terran_slayer_protoss_b' => [
        'name'        => 'You Should Have Stayed Home',
        'description' => 'Win 25 games as Terran against Protoss',
        'tier'        => 'b',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'terran_slayer_protoss',
        'lore'        => null,
    ],

    'terran_slayer_protoss_a' => [
        'name'        => 'Aiur? Never Heard Of It',
        'description' => 'Win 50 games as Terran against Protoss',
        'tier'        => 'a',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'terran_slayer_protoss',
        'lore'        => null,
    ],

    'terran_slayer_protoss_s' => [
        'name'        => 'Sic Transit Gloria Protoss',
        'description' => 'Win 100 games as Terran against Protoss',
        'tier'        => 's',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'terran_slayer_protoss',
        'lore'        => 'Adapted from the Latin phrase "Sic transit gloria mundi" — "Thus passes the glory of the world." ',
    ],

    // -------------------------------------------------------------------------
    // Community
    // -------------------------------------------------------------------------
    'around_the_world' => [
        'name'        => 'Around the World',
        'description' => 'Play opponents from South America, Europe, and Asia in a single calendar month.',
        'tier'        => 'b',
        'category'    => 'community',
        'secret'      => false,
        'group'       => null,
        'lore'        => null,
    ],
    'traveler' => [
        'name'        => 'First Contact',
        'description' => 'Play against players from 5 different countries',
        'tier'        => 'd',
        'category'    => 'community',
        'secret'      => false,
        'group'       => 'countries',
        'lore'        => null,
    ],
    'explorer' => [
        'name'        => 'Deep Space',
        'description' => 'Play against players from 10 different countries',
        'tier'        => 'c',
        'category'    => 'community',
        'secret'      => false,
        'group'       => 'countries',
        'lore'        => null,
    ],
    'globetrotter' => [
        'name'        => 'Sector Commander',
        'description' => 'Play against players from 20 different countries',
        'tier'        => 'b',
        'category'    => 'community',
        'secret'      => false,
        'group'       => 'countries',
        'lore'        => null,
    ],
    'citizen_of_the_universe' => [
        'name'        => 'Citizen of the Universe',
        'description' => 'Play against players from 30 different countries',
        'tier'        => 'a',
        'category'    => 'community',
        'secret'      => false,
        'group'       => 'countries',
        'lore'        => null,
    ],
    'koprulu_cartographer' => [
        'name'        => 'Koprulu Cartographer',
        'description' => 'Play against players from 40 different countries',
        'tier'        => 's',
        'category'    => 'community',
        'secret'      => false,
        'group'       => 'countries',
        'lore'        => null,
    ],
    'circuit_player' => [
        'name'        => 'Skirmish',
        'description' => 'Five battles fought.',
        'tier'        => 'd',
        'category'    => 'community',
        'secret'      => false,
        'group'       => 'tournaments',
        'lore'        => null,
    ],
    'road_warrior' => [
        'name'        => 'Campaign',
        'description' => 'Twenty-five battles fought.',
        'tier'        => 'c',
        'category'    => 'community',
        'secret'      => false,
        'group'       => 'tournaments',
        'lore'        => null,
    ],
    'legend_of_the_circuit' => [
        'name'        => 'Warlord',
        'description' => 'A hundred battles fought.',
        'tier'        => 'b',
        'category'    => 'community',
        'secret'      => false,
        'group'       => 'tournaments',
        'lore'        => null,
    ],
    'conqueror' => [
        'name'        => 'Conqueror',
        'description' => 'Two hundred and fifty battles fought.',
        'tier'        => 'a',
        'category'    => 'community',
        'secret'      => false,
        'group'       => 'tournaments',
        'lore'        => null,
    ],
    'war_boy' => [
        'name'        => 'War Boy',
        'description' => 'Five hundred battles fought. On the Fury Road.',
        'tier'        => 's',
        'category'    => 'community',
        'secret'      => false,
        'group'       => 'tournaments',
        'lore'        => 'Mad Max: Fury Road (2015). The War Boys are fanatical soldiers who worship Immortan Joe and seek a glorious death in combat, screaming "Witness me!" as they go.',
    ],

    // -------------------------------------------------------------------------
    // History
    // -------------------------------------------------------------------------
    'og' => [
        'name'        => 'OG',
        'description' => 'One of the first 500 players in the database',
        'tier'        => 'a',
        'category'    => 'history',
        'secret'      => false,
        'group'       => 'founding',
        'lore'        => null,
    ],
    'founding_father' => [
        'name'        => 'Founding Father',
        'description' => 'One of the first 50 players in the database',
        'tier'        => 'a',
        'category'    => 'history',
        'secret'      => false,
        'group'       => 'founding',
        'lore'        => null,
    ],
    'time_traveler' => [
        'name'        => 'Time Traveler',
        'description' => 'Have games from 3+ different years',
        'tier'        => 'd',
        'category'    => 'history',
        'secret'      => false,
        'group'       => null,
        'lore'        => null,
    ],
    'old_breed' => [
        'name'        => 'Old Breed',
        'description' => 'Play a game in 2017 or earlier.',
        'tier'        => 'c',
        'category'    => 'history',
        'secret'      => false,
        'group'       => null,
        'lore' => 'A reference to "With the Old Breed: At Peleliu and Okinawa" (1981) — often considered one of the finest WWII memoirs. Eugene Sledge describes his service with the 1st Marine Division during some of the Pacific War’s harshest fighting. Later adapted into HBO’s "The Pacific," produced by Steven Spielberg and Tom Hanks.',
    ],
    'before_the_plague' => [
        'name'        => 'Before the Plague',
        'description' => 'Before the swarm arrived. Play a game before 2020.',
        'tier'        => 'c',
        'category'    => 'history',
        'secret'      => false,
        'group'       => null,
        'lore'        => null,
    ],
    'patient_zero' => [
        'name'        => 'Patient Zero',
        'description' => 'Playing through the plague. Play a game in 2020.',
        'tier'        => 'c',
        'category'    => 'history',
        'secret'      => false,
        'group'       => null,
        'lore' => 'A reference to the COVID-19 pandemic of 2020.',
    ],
    'plague_survivor' => [
        'name'        => 'Plague Survivor',
        'description' => 'The swarm could not stop you. Play a game before and after 2020.',
        'tier'        => 'a',
        'category'    => 'history',
        'secret'      => false,
        'group'       => null,
        'lore'        => 'A reference to the COVID-19 pandemic of 2020.',
    ],

    // -------------------------------------------------------------------------
    // Comebacks & drama
    // -------------------------------------------------------------------------
    'redemption' => [
        'name'        => 'Redemption',
        'description' => 'Lose 5 in a row then win 5 in a row',
        'tier'        => 'c',
        'category'    => 'drama',
        'secret'      => false,
        'group'       => null,
        'lore'        => null,
    ],

    'against_all_odds_10' => [
        'name'        => 'David',
        'description' => 'Beat a top 10 player while being outside top 50 (both players must have 30+ games)',
        'tier'        => 'a',
        'category'    => 'drama',
        'secret'      => false,
        'group'       => 'against_all_odds',
        'lore'        => null,
    ],
    'against_all_odds' => [
        'name'        => 'Against All Odds',
        'description' => 'Beat a top 3 player while being outside top 50 (both players must have 30+ games)',
        'tier'        => 's',
        'category'    => 'drama',
        'secret'      => false,
        'group'       => 'against_all_odds',
        'lore'        => null,
    ],
    'upset_king' => [
        'name'        => 'Upset King',
        'description' => 'Beat a player 200+ higher than you 5 times',
        'tier'        => 'a',
        'category'    => 'drama',
        'secret'      => false,
        'group'       => null,
        'lore'        => null,
    ],

    // -------------------------------------------------------------------------
    // Calendar
    // -------------------------------------------------------------------------
    'night_shift' => [
        'name'        => 'Night Shift',
        'description' => 'The grind begins. 5 active days in a month.',
        'tier'        => 'c',
        'category'    => 'calendar',
        'secret'      => false,
        'group'       => 'active_days',
        'lore'        => null,
    ],
    'no_days_off' => [
        'name'        => 'No Days Off',
        'description' => 'Sleep is for the weak. 10 active days in a month.',
        'tier'        => 'b',
        'category'    => 'calendar',
        'secret'      => false,
        'group'       => 'active_days',
        'lore'        => null,
    ],
    'workaholic' => [
        'name'        => 'Workaholic',
        'description' => 'What is a weekend? 20 active days in a month.',
        'tier'        => 's',
        'category'    => 'calendar',
        'secret'      => false,
        'group'       => 'active_days',
        'lore'        => null,
    ],

    // -------------------------------------------------------------------------
    // Prestige
    // -------------------------------------------------------------------------
    'the_goat' => [
        'name'        => 'The GOAT',
        'description' => 'Be #1 for a total of 12 months',
        'tier'        => 's',
        'category'    => 'prestige',
        'secret'      => false,
        'group'       => null,
        'lore'        => null,
    ],
    'early_pressure' => [
        'name'        => 'Early Pressure',
        'description' => 'Deny the expansion. Win your first 3 games in a row.',
        'tier'        => 'b',
        'category'    => 'prestige',
        'secret'      => false,
        'group'       => 'perfect_start',
        'lore'        => null,
    ],
    'all_in' => [
        'name'        => 'All In',
        'description' => 'No going back. Win your first 5 games in a row.',
        'tier'        => 'a',
        'category'    => 'prestige',
        'secret'      => false,
        'group'       => 'perfect_start',
        'lore'        => null,
    ],
    'perfect_start' => [
        'name'        => 'Perfect Start',
        'description' => 'Flawless execution. Win your first 10 games in a row.',
        'tier'        => 's',
        'category'    => 'prestige',
        'secret'      => false,
        'group'       => 'perfect_start',
        'lore'        => null,
    ],

    // -------------------------------------------------------------------------
    // Secret achievements
    // -------------------------------------------------------------------------
    'new_year' => [
        'name'        => 'New Year',
        'description' => 'Play a game on January 1st',
        'tier'        => 'a',
        'category'    => 'secret',
        'secret'      => true,
        'group'       => null,
        'lore'        => null,
    ],
    'valentines' => [
        'name'        => "Valentine's",
        'description' => 'Play a game on February 14th',
        'tier'        => 'a',
        'category'    => 'secret',
        'secret'      => true,
        'group'       => null,
        'lore'        => null,
    ],
    'christmas' => [
        'name'        => 'Christmas',
        'description' => 'Play a game on December 25th',
        'tier'        => 'a',
        'category'    => 'secret',
        'secret'      => true,
        'group'       => null,
        'lore'        => null,
    ],
    'halloween' => [
        'name'        => 'Halloween',
        'description' => 'Play a game on October 31st',
        'tier'        => 'a',
        'category'    => 'secret',
        'secret'      => true,
        'group'       => null,
        'lore'        => null,
    ],
    'sc_birthday' => [
        'name'        => 'StarCraft Birthday',
        'description' => 'Play a game on March 31st — StarCraft\'s anniversary',
        'tier'        => 'a',
        'category'    => 'secret',
        'secret'      => true,
        'group'       => null,
        'lore'        => null,
    ],
    'bw_birthday' => [
        'name'        => 'Brood War Birthday',
        'description' => 'Play a game on November 30th — Brood War\'s anniversary',
        'tier'        => 'a',
        'category'    => 'secret',
        'secret'      => true,
        'group'       => null,
        'lore'        => null,
    ],
    'remastered_birthday' => [
        'name'        => 'Remastered Birthday',
        'description' => 'Play a game on August 14th — StarCraft Remastered\'s anniversary',
        'tier'        => 'a',
        'category'    => 'secret',
        'secret'      => true,
        'group'       => null,
        'lore'        => null,
    ],
    'mirror_match' => [
        'name'        => 'Mirror Match',
        'description' => 'Play against a player with the exact same rating as you',
        'tier'        => 'd',
        'category'    => 'secret',
        'secret'      => true,
        'group'       => null,
        'lore'        => null,
    ],
    'beast_mode' => [
        'name'        => 'Beast Mode',
        'description' => 'Play your 666th game',
        'tier'        => 'a',
        'category'    => 'secret',
        'secret'      => true,
        'group'       => null,
        'lore'        => null,
    ],
    'millennium' => [
        'name'        => 'Y2K',
        'description' => '2000 games. The computers survived. So did you.',
        'tier'        => 'a',
        'category'    => 'secret',
        'secret'      => true,
        'group'       => null,
        'lore'        => 'The global panic about whether computers would crash when the year hit 2000. Billions were spent on fixes. On January 1st, almost nothing happened.',
    ],
    'back_to_square_one' => [
        'name'        => 'Back to Square One',
        'description' => 'End a game with exactly 1000 rating',
        'tier'        => 'c',
        'category'    => 'secret',
        'secret'      => true,
        'group'       => null,
        'lore'        => null,
    ],
    'bad_day' => [
        'name'        => "It's Just a Bad Day",
        'description' => 'Lose 10 games in a row',
        'tier'        => 'd',
        'category'    => 'secret',
        'secret'      => true,
        'group'       => 'loss_streak',
        'lore'        => null,
    ],
    'different_game' => [
        'name'        => 'Maybe Try a Different Game?',
        'description' => 'Lose 20 games in a row',
        'tier'        => 'c',
        'category'    => 'secret',
        'secret'      => true,
        'group'       => 'loss_streak',
        'lore'        => null,
    ],
    'dedicated_to_the_cause' => [
        'name'        => 'Dedicated to the Cause',
        'description' => 'Lose 30 games in a row',
        'tier'        => 's',
        'category'    => 'secret',
        'secret'      => true,
        'group'       => 'loss_streak',
        'lore'        => null,
    ],
    'how' => [
        'name'        => 'How?!',
        'description' => 'Lose to a player rated 500+ lower than you (both players must have 30+ games)',
        'tier'        => 'd',
        'category'    => 'secret',
        'secret'      => true,
        'group'       => null,
        'lore'        => null,
    ],
    'rookie_mistake' => [
        'name'        => 'Rookie Mistake',
        'description' => 'Lose your very first ranked game',
        'tier'        => 'd',
        'category'    => 'secret',
        'secret'      => true,
        'group'       => null,
        'lore'        => null,
    ],

];