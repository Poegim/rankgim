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
    'first_blood' => [
        'name'        => 'First Blood',
        'description' => 'Play 15 games',
        'tier'        => 'd',
        'category'    => 'games',
        'secret'      => false,
    ],
    'apprentice' => [
        'name'        => 'Apprentice',
        'description' => 'Play 50 games',
        'tier'        => 'd',
        'category'    => 'games',
        'secret'      => false,
    ],
    'veteran' => [
        'name'        => 'Veteran',
        'description' => 'Play 100 games',
        'tier'        => 'c',
        'category'    => 'games',
        'secret'      => false,
    ],
    'gladiator' => [
        'name'        => 'Gladiator',
        'description' => 'Play 250 games',
        'tier'        => 'b',
        'category'    => 'games',
        'secret'      => false,
    ],
    'warlord' => [
        'name'        => 'Warlord',
        'description' => 'Play 500 games',
        'tier'        => 'a',
        'category'    => 'games',
        'secret'      => false,
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
    ],
    'dedicated' => [
        'name'        => 'Dedicated',
        'description' => 'Be active for 24 months in total',
        'tier'        => 'c',
        'category'    => 'activity',
        'secret'      => false,
    ],
    'committed' => [
        'name'        => 'Committed',
        'description' => 'Be active for 36 months in total',
        'tier'        => 'b',
        'category'    => 'activity',
        'secret'      => false,
    ],
    'obsessed' => [
        'name'        => 'Obsessed',
        'description' => 'Be active for 48 months in total',
        'tier'        => 'a',
        'category'    => 'activity',
        'secret'      => false,
    ],
    'immortal' => [
        'name'        => 'Immortal',
        'description' => 'Be active for 60 months in total',
        'tier'        => 's',
        'category'    => 'activity',
        'secret'      => false,
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
    ],
    'consistent' => [
        'name'        => 'Consistent',
        'description' => 'Be active for 6 consecutive months',
        'tier'        => 'c',
        'category'    => 'activity',
        'secret'      => false,
    ],
    'unstoppable_activity' => [
        'name'        => 'Unstoppable',
        'description' => 'Be active for 12 consecutive months',
        'tier'        => 'b',
        'category'    => 'activity',
        'secret'      => false,
    ],
    'machine' => [
        'name'        => 'Machine',
        'description' => 'Be active for 24 consecutive months',
        'tier'        => 's',
        'category'    => 'activity',
        'secret'      => false,
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
    ],
    'top_100' => [
        'name'        => 'Top 100',
        'description' => 'Reach top 100',
        'tier'        => 'd',
        'category'    => 'ranking',
        'secret'      => false,
    ],
    'top_50' => [
        'name'        => 'Top 50',
        'description' => 'Reach top 50',
        'tier'        => 'c',
        'category'    => 'ranking',
        'secret'      => false,
    ],
    'top_25' => [
        'name'        => 'Top 25',
        'description' => 'Reach top 25',
        'tier'        => 'c',
        'category'    => 'ranking',
        'secret'      => false,
    ],
    'elite' => [
        'name'        => 'Elite',
        'description' => 'Reach top 10',
        'tier'        => 'b',
        'category'    => 'ranking',
        'secret'      => false,
    ],
    'podium' => [
        'name'        => 'Podium',
        'description' => 'Reach top 3',
        'tier'        => 'a',
        'category'    => 'ranking',
        'secret'      => false,
    ],
    'the_best' => [
        'name'        => 'The Best',
        'description' => 'Reach #1',
        'tier'        => 's',
        'category'    => 'ranking',
        'secret'      => false,
    ],

    // -------------------------------------------------------------------------
    // Ranking — time in top 10 (total months, not consecutive)
    // -------------------------------------------------------------------------
    'fixture' => [
        'name'        => 'Fixture',
        'description' => 'Spend 3 months in top 10',
        'tier'        => 'c',
        'category'    => 'ranking',
        'secret'      => false,
    ],
    'pillar' => [
        'name'        => 'Pillar',
        'description' => 'Spend 6 months in top 10',
        'tier'        => 'b',
        'category'    => 'ranking',
        'secret'      => false,
    ],
    'institution' => [
        'name'        => 'Institution',
        'description' => 'Spend 12 months in top 10',
        'tier'        => 'a',
        'category'    => 'ranking',
        'secret'      => false,
    ],
    'monument' => [
        'name'        => 'Monument',
        'description' => 'Spend 24 months in top 10',
        'tier'        => 's',
        'category'    => 'ranking',
        'secret'      => false,
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
    ],
    'legends_return' => [
        'name'        => "Legend's Return",
        'description' => 'Return to top 10 after 12+ months away',
        'tier'        => 'a',
        'category'    => 'ranking',
        'secret'      => false,
    ],
    'ghosts_return' => [
        'name'        => "Ghost's Return",
        'description' => 'Return to top 10 after 24+ months away',
        'tier'        => 's',
        'category'    => 'ranking',
        'secret'      => false,
    ],

    // -------------------------------------------------------------------------
    // Rating milestones
    // -------------------------------------------------------------------------
    'rising'       => ['name' => 'Rising',      'description' => 'Reach 1100 rating', 'tier' => 'd', 'category' => 'rating', 'secret' => false],
    'solid'        => ['name' => 'Solid',        'description' => 'Reach 1200 rating', 'tier' => 'd', 'category' => 'rating', 'secret' => false],
    'strong'       => ['name' => 'Strong',       'description' => 'Reach 1300 rating', 'tier' => 'c', 'category' => 'rating', 'secret' => false],
    'dangerous'    => ['name' => 'Dangerous',    'description' => 'Reach 1400 rating', 'tier' => 'c', 'category' => 'rating', 'secret' => false],
    'fearsome'     => ['name' => 'Fearsome',     'description' => 'Reach 1500 rating', 'tier' => 'b', 'category' => 'rating', 'secret' => false],
    'dominant'     => ['name' => 'Dominant',     'description' => 'Reach 1600 rating', 'tier' => 'b', 'category' => 'rating', 'secret' => false],
    'elite_rating' => ['name' => 'Elite',        'description' => 'Reach 1700 rating', 'tier' => 'a', 'category' => 'rating', 'secret' => false],
    'terrifying'   => ['name' => 'Terrifying',   'description' => 'Reach 1800 rating', 'tier' => 'a', 'category' => 'rating', 'secret' => false],
    'legendary'    => ['name' => 'Legendary',    'description' => 'Reach 1900 rating', 'tier' => 's', 'category' => 'rating', 'secret' => false],
    'mythical'     => ['name' => 'Mythical',     'description' => 'Reach 2000 rating', 'tier' => 's', 'category' => 'rating', 'secret' => false],
    'transcendent' => ['name' => 'Transcendent', 'description' => 'Reach 2100 rating', 'tier' => 's', 'category' => 'rating', 'secret' => false],
    'otherworldly' => ['name' => 'Otherworldly', 'description' => 'Reach 2200 rating', 'tier' => 's', 'category' => 'rating', 'secret' => false],
    'god_tier'     => ['name' => 'God-tier',     'description' => 'Reach 2300+ rating','tier' => 's', 'category' => 'rating', 'secret' => false],

    'giant_slayer' => [
        'name'        => 'Giant Slayer',
        'description' => 'Beat a player rated 200+ higher than you',
        'tier'        => 'c',
        'category'    => 'rating',
        'secret'      => false,
    ],
    'david_vs_goliath' => [
        'name'        => 'David vs Goliath',
        'description' => 'Beat a player rated 300+ higher than you',
        'tier'        => 'b',
        'category'    => 'rating',
        'secret'      => false,
    ],
    'rocket' => [
        'name'        => 'Rocket',
        'description' => 'Gain 100+ rating points in a single month',
        'tier'        => 'b',
        'category'    => 'rating',
        'secret'      => false,
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
    ],
    'hot_streak' => [
        'name'        => 'Hot Streak',
        'description' => 'Win 5 games in a row',
        'tier'        => 'd',
        'category'    => 'streaks',
        'secret'      => false,
    ],
    'rampage' => [
        'name'        => 'Rampage',
        'description' => 'Win 10 games in a row',
        'tier'        => 'c',
        'category'    => 'streaks',
        'secret'      => false,
    ],
    'unstoppable_streak' => [
        'name'        => 'Unstoppable',
        'description' => 'Win 15 games in a row',
        'tier'        => 'b',
        'category'    => 'streaks',
        'secret'      => false,
    ],
    'juggernaut' => [
        'name'        => 'Juggernaut',
        'description' => 'Win 25 games in a row',
        'tier'        => 'a',
        'category'    => 'streaks',
        'secret'      => false,
    ],
    'terminator' => [
        'name'        => 'Terminator',
        'description' => 'Win 50 games in a row',
        'tier'        => 's',
        'category'    => 'streaks',
        'secret'      => false,
    ],
    'phoenix' => [
        'name'        => 'Phoenix',
        'description' => 'Return to playing after 12+ months away',
        'tier'        => 'c',
        'category'    => 'streaks',
        'secret'      => false,
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
    ],
    'cursed' => [
        'name'        => 'Cursed',
        'description' => 'Lose to the same player 10 times',
        'tier'        => 'c',
        'category'    => 'rivalry',
        'secret'      => false,
    ],
    'bully' => [
        'name'        => 'Bully',
        'description' => 'Beat the same player 10 times',
        'tier'        => 'c',
        'category'    => 'rivalry',
        'secret'      => false,
    ],
    'executioner' => [
        'name'        => 'Executioner',
        'description' => 'Beat the same player 20 times',
        'tier'        => 'b',
        'category'    => 'rivalry',
        'secret'      => false,
    ],
    'the_rematch' => [
        'name'        => 'The Rematch',
        'description' => 'Play the same player 30+ times',
        'tier'        => 'c',
        'category'    => 'rivalry',
        'secret'      => false,
    ],
    'the_rivalry' => [
        'name'        => 'The Rivalry',
        'description' => 'Play the same player 50+ times',
        'tier'        => 'b',
        'category'    => 'rivalry',
        'secret'      => false,
    ],

    // -------------------------------------------------------------------------
    // Community
    // -------------------------------------------------------------------------
    'traveler' => [
        'name'        => 'Traveler',
        'description' => 'Play against players from 5 different countries',
        'tier'        => 'd',
        'category'    => 'community',
        'secret'      => false,
    ],
    'explorer' => [
        'name'        => 'Explorer',
        'description' => 'Play against players from 10 different countries',
        'tier'        => 'c',
        'category'    => 'community',
        'secret'      => false,
    ],
    'globetrotter' => [
        'name'        => 'Globetrotter',
        'description' => 'Play against players from 20 different countries',
        'tier'        => 'b',
        'category'    => 'community',
        'secret'      => false,
    ],
    'tournament_debut' => [
        'name'        => 'Tournament Debut',
        'description' => 'Play in your first tournament',
        'tier'        => 'd',
        'category'    => 'community',
        'secret'      => false,
    ],
    'circuit_player' => [
        'name'        => 'Circuit Player',
        'description' => 'Play in 5 tournaments',
        'tier'        => 'c',
        'category'    => 'community',
        'secret'      => false,
    ],
    'road_warrior' => [
        'name'        => 'Road Warrior',
        'description' => 'Play in 10 tournaments',
        'tier'        => 'b',
        'category'    => 'community',
        'secret'      => false,
    ],
    'legend_of_the_circuit' => [
        'name'        => 'Legend of the Circuit',
        'description' => 'Play in 25 tournaments',
        'tier'        => 'a',
        'category'    => 'community',
        'secret'      => false,
    ],

    // -------------------------------------------------------------------------
    // History
    // -------------------------------------------------------------------------
    'og' => [
        'name'        => 'OG',
        'description' => 'One of the first 500 players in the database',
        'tier'        => 'c',
        'category'    => 'history',
        'secret'      => false,
    ],
    'founding_father' => [
        'name'        => 'Founding Father',
        'description' => 'One of the first 50 players in the database',
        'tier'        => 'b',
        'category'    => 'history',
        'secret'      => false,
    ],
    'time_traveler' => [
        'name'        => 'Time Traveler',
        'description' => 'Have games from 3+ different years',
        'tier'        => 'd',
        'category'    => 'history',
        'secret'      => false,
    ],
    'dinosaur' => [
        'name'        => 'Dinosaur',
        'description' => 'In the database for 5+ years and still active',
        'tier'        => 'a',
        'category'    => 'history',
        'secret'      => false,
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
    ],
    'rollercoaster' => [
        'name'        => 'Rollercoaster',
        'description' => 'Lose 100+ rating points and recover them in the same month',
        'tier'        => 'b',
        'category'    => 'drama',
        'secret'      => false,
    ],
    'against_all_odds' => [
        'name'        => 'Against All Odds',
        'description' => 'Beat a top 3 player while being outside top 50',
        'tier'        => 'a',
        'category'    => 'drama',
        'secret'      => false,
    ],
    'upset_king' => [
        'name'        => 'Upset King',
        'description' => 'Beat a player 200+ higher than you 5 times',
        'tier'        => 'a',
        'category'    => 'drama',
        'secret'      => false,
    ],

    // -------------------------------------------------------------------------
    // Calendar
    // -------------------------------------------------------------------------
    'marathon' => [
        'name'        => 'Marathon',
        'description' => 'Play 10+ games in a single day',
        'tier'        => 'c',
        'category'    => 'calendar',
        'secret'      => false,
    ],
    'workaholic' => [
        'name'        => 'Workaholic',
        'description' => 'Play on 20 different days in a single month',
        'tier'        => 'b',
        'category'    => 'calendar',
        'secret'      => false,
    ],
    'weekend_warrior' => [
        'name'        => 'Weekend Warrior',
        'description' => 'Play 5+ games on a Saturday or Sunday',
        'tier'        => 'd',
        'category'    => 'calendar',
        'secret'      => false,
    ],
    'hat_trick_month' => [
        'name'        => 'Hat Trick Month',
        'description' => 'Play at least 1 game every day for 30 days in a row',
        'tier'        => 'a',
        'category'    => 'calendar',
        'secret'      => false,
    ],

    // -------------------------------------------------------------------------
    // Peak hunting
    // -------------------------------------------------------------------------
    'peak_chaser' => [
        'name'        => 'Peak Chaser',
        'description' => 'Beat your personal rating record for the first time',
        'tier'        => 'd',
        'category'    => 'peak',
        'secret'      => false,
    ],
    'new_heights' => [
        'name'        => 'New Heights',
        'description' => 'Beat your personal rating record 3 times',
        'tier'        => 'c',
        'category'    => 'peak',
        'secret'      => false,
    ],
    'ceiling_breaker' => [
        'name'        => 'Ceiling Breaker',
        'description' => 'Beat your personal rating record 5 times',
        'tier'        => 'b',
        'category'    => 'peak',
        'secret'      => false,
    ],

    // -------------------------------------------------------------------------
    // Precision
    // -------------------------------------------------------------------------
    'efficient' => [
        'name'        => 'Efficient',
        'description' => 'Maintain 70%+ win rate with at least 50 games',
        'tier'        => 'b',
        'category'    => 'precision',
        'secret'      => false,
    ],
    'consistent_killer' => [
        'name'        => 'Consistent Killer',
        'description' => 'Maintain 60%+ win rate for 3 consecutive months',
        'tier'        => 'b',
        'category'    => 'precision',
        'secret'      => false,
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
    ],
    'perfect_start' => [
        'name'        => 'Perfect Start',
        'description' => 'Win your first 10 games in a row',
        'tier'        => 'b',
        'category'    => 'prestige',
        'secret'      => false,
    ],

    // -------------------------------------------------------------------------
    // Secret achievements
    // -------------------------------------------------------------------------
    'new_year' => [
        'name'        => 'New Year',
        'description' => 'Play a game on January 1st',
        'tier'        => 'd',
        'category'    => 'secret',
        'secret'      => true,
    ],
    'valentines' => [
        'name'        => "Valentine's",
        'description' => 'Play a game on February 14th',
        'tier'        => 'd',
        'category'    => 'secret',
        'secret'      => true,
    ],
    'christmas' => [
        'name'        => 'Christmas',
        'description' => 'Play a game on December 25th',
        'tier'        => 'd',
        'category'    => 'secret',
        'secret'      => true,
    ],
    'halloween' => [
        'name'        => 'Halloween',
        'description' => 'Play a game on October 31st',
        'tier'        => 'd',
        'category'    => 'secret',
        'secret'      => true,
    ],
    'mirror_match' => [
        'name'        => 'Mirror Match',
        'description' => 'Play against a player with the exact same rating as you',
        'tier'        => 'd',
        'category'    => 'secret',
        'secret'      => true,
    ],
    'beast_mode' => [
        'name'        => 'Beast Mode',
        'description' => 'Play your 666th game',
        'tier'        => 'd',
        'category'    => 'secret',
        'secret'      => true,
    ],
    'millennium' => [
        'name'        => 'Millennium',
        'description' => 'Play your 1000th game',
        'tier'        => 'b',
        'category'    => 'secret',
        'secret'      => true,
    ],
    'initials' => [
        'name'        => 'Initials',
        'description' => 'Your name starts with the same letter as your country',
        'tier'        => 'd',
        'category'    => 'secret',
        'secret'      => true,
    ],
    'world_tour' => [
        'name'        => 'World Tour',
        'description' => 'Play against players from EU, NA, SA and Asia',
        'tier'        => 'c',
        'category'    => 'secret',
        'secret'      => true,
    ],
    'back_to_square_one' => [
        'name'        => 'Back to Square One',
        'description' => 'End a game with exactly 1000 rating',
        'tier'        => 'c',
        'category'    => 'secret',
        'secret'      => true,
    ],
    'fifty_fifty' => [
        'name'        => 'Fifty Fifty',
        'description' => 'Have exactly 50% win rate after exactly 100 games',
        'tier'        => 'c',
        'category'    => 'secret',
        'secret'      => true,
    ],

    // Ironic secrets
    'bad_day' => [
        'name'        => "It's Just a Bad Day",
        'description' => 'Lose 10 games in a row',
        'tier'        => 'd',
        'category'    => 'secret',
        'secret'      => true,
    ],
    'different_game' => [
        'name'        => 'Maybe Try a Different Game?',
        'description' => 'Lose 20 games in a row',
        'tier'        => 'c',
        'category'    => 'secret',
        'secret'      => true,
    ],
    'dedicated_to_the_cause' => [
        'name'        => 'Dedicated to the Cause',
        'description' => 'Lose 30 games in a row',
        'tier'        => 'b',
        'category'    => 'secret',
        'secret'      => true,
    ],
    'how' => [
        'name'        => 'How?!',
        'description' => 'Lose to a player rated 500+ lower than you',
        'tier'        => 'd',
        'category'    => 'secret',
        'secret'      => true,
    ],
    'rookie_mistake' => [
        'name'        => 'Rookie Mistake',
        'description' => 'Lose your very first ranked game',
        'tier'        => 'd',
        'category'    => 'secret',
        'secret'      => true,
    ],

];