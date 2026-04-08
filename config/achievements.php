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
    ],
    'rocky' => [
        'name'        => 'Rocky',
        'description' => '75 games. Nobody hits as hard as life.',
        'tier'        => 'c',
        'category'    => 'games',
        'secret'      => false,
        'group'       => 'games_played',
    ],
    'inception' => [
        'name'        => 'Inception',
        'description' => '250 games. How deep does the rabbit hole go?',
        'tier'        => 'b',
        'category'    => 'games',
        'secret'      => false,
        'group'       => 'games_played',
    ],
    'there_is_no_spoon' => [
        'name'        => 'There Is No Spoon',
        'description' => '500 games. There is no free time either.',
        'tier'        => 'a',
        'category'    => 'games',
        'secret'      => false,
        'group'       => 'games_played',
    ],
    'tears_in_rain' => [
        'name'        => 'Tears in Rain',
        'description' => '1000 games. I\'ve seen things you people wouldn\'t believe. All those moments will be lost in time, like tears in rain.',
        'tier'        => 's',
        'category'    => 'games',
        'secret'      => false,
        'group'       => 'games_played',
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
    ],
    'dedicated' => [
        'name'        => 'Dedicated',
        'description' => 'Be active for 24 months in total',
        'tier'        => 'c',
        'category'    => 'activity',
        'secret'      => false,
        'group'       => 'activity_total',
    ],
    'committed' => [
        'name'        => 'Committed',
        'description' => 'Be active for 36 months in total',
        'tier'        => 'b',
        'category'    => 'activity',
        'secret'      => false,
        'group'       => 'activity_total',
    ],
    'obsessed' => [
        'name'        => 'Obsessed',
        'description' => 'Be active for 48 months in total',
        'tier'        => 'a',
        'category'    => 'activity',
        'secret'      => false,
        'group'       => 'activity_total',
    ],
    'immortal' => [
        'name'        => 'Immortal',
        'description' => 'Be active for 60 months in total',
        'tier'        => 's',
        'category'    => 'activity',
        'secret'      => false,
        'group'       => 'activity_total',
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
    ],
    'consistent' => [
        'name'        => 'Consistent',
        'description' => 'Be active for 6 consecutive months',
        'tier'        => 'c',
        'category'    => 'activity',
        'secret'      => false,
        'group'       => 'activity_streak',
    ],
    'unstoppable_activity' => [
        'name'        => 'Unstoppable',
        'description' => 'Be active for 12 consecutive months',
        'tier'        => 'b',
        'category'    => 'activity',
        'secret'      => false,
        'group'       => 'activity_streak',
    ],
        'iron_will' => [
        'name'        => 'Iron Will',
        'description' => 'Be active for 18 consecutive months',
        'tier'        => 'a',
        'category'    => 'activity',
        'secret'      => false,
        'group'       => 'activity_streak',
    ],
    'machine' => [
        'name'        => 'Machine',
        'description' => 'Be active for 24 consecutive months',
        'tier'        => 's',
        'category'    => 'activity',
        'secret'      => false,
        'group'       => 'activity_streak',
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
    ],
    'top_100' => [
        'name'        => 'Top 100',
        'description' => 'Reach top 100',
        'tier'        => 'd',
        'category'    => 'ranking',
        'secret'      => false,
        'group'       => 'top_rank',
    ],
    'top_50' => [
        'name'        => 'Top 50',
        'description' => 'Reach top 50',
        'tier'        => 'c',
        'category'    => 'ranking',
        'secret'      => false,
        'group'       => 'top_rank',
    ],
    'top_25' => [
        'name'        => 'Top 25',
        'description' => 'Reach top 25',
        'tier'        => 'b',
        'category'    => 'ranking',
        'secret'      => false,
        'group'       => 'top_rank',
    ],
    'elite' => [
        'name'        => 'Elite',
        'description' => 'Reach top 10',
        'tier'        => 'a',
        'category'    => 'ranking',
        'secret'      => false,
        'group'       => 'top_rank',
    ],
    'podium' => [
        'name'        => 'Podium',
        'description' => 'Reach top 3',
        'tier'        => 'a',
        'category'    => 'ranking',
        'secret'      => false,
        'group'       => 'top_rank',
    ],
    'the_best' => [
        'name'        => 'The Best',
        'description' => 'Reach #1',
        'tier'        => 's',
        'category'    => 'ranking',
        'secret'      => false,
        'group'       => 'top_rank',
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
    ],
    'pillar' => [
        'name'        => 'Pillar',
        'description' => 'Spend 6 months in top 10',
        'tier'        => 'b',
        'category'    => 'ranking',
        'secret'      => false,
        'group'       => 'top10_time',
    ],
    'institution' => [
        'name'        => 'Institution',
        'description' => 'Spend 12 months in top 10',
        'tier'        => 'a',
        'category'    => 'ranking',
        'secret'      => false,
        'group'       => 'top10_time',
    ],
    'monument' => [
        'name'        => 'Monument',
        'description' => 'Spend 24 months in top 10',
        'tier'        => 's',
        'category'    => 'ranking',
        'secret'      => false,
        'group'       => 'top10_time',
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
    ],
    'legends_return' => [
        'name'        => "Legend's Return",
        'description' => 'Return to top 10 after 12+ months away',
        'tier'        => 'a',
        'category'    => 'ranking',
        'secret'      => false,
        'group'       => 'kings_return',
    ],
    'ghosts_return' => [
        'name'        => "Ghost's Return",
        'description' => 'Return to top 10 after 24+ months away',
        'tier'        => 's',
        'category'    => 'ranking',
        'secret'      => false,
        'group'       => 'kings_return',
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
    ],
    'solid' => [
        'name'        => 'Solid',
        'description' => 'Reach 1200 rating',
        'tier'        => 'd',
        'category'    => 'rating',
        'secret'      => false,
        'group'       => 'rating_milestone',
    ],
    'strong' => [
        'name'        => 'Strong',
        'description' => 'Reach 1300 rating',
        'tier'        => 'c',
        'category'    => 'rating',
        'secret'      => false,
        'group'       => 'rating_milestone',
    ],
    'dangerous' => [
        'name'        => 'Dangerous',
        'description' => 'Reach 1400 rating',
        'tier'        => 'c',
        'category'    => 'rating',
        'secret'      => false,
        'group'       => 'rating_milestone',
    ],
    'fearsome' => [
        'name'        => 'Fearsome',
        'description' => 'Reach 1500 rating',
        'tier'        => 'b',
        'category'    => 'rating',
        'secret'      => false,
        'group'       => 'rating_milestone',
    ],
    'dominant' => [
        'name'        => 'Dominant',
        'description' => 'Reach 1600 rating',
        'tier'        => 'b',
        'category'    => 'rating',
        'secret'      => false,
        'group'       => 'rating_milestone',
    ],
    'elite_rating' => [
        'name'        => 'Elite',
        'description' => 'Reach 1700 rating',
        'tier'        => 'a',
        'category'    => 'rating',
        'secret'      => false,
        'group'       => 'rating_milestone',
    ],
    'terrifying' => [
        'name'        => 'Terrifying',
        'description' => 'Reach 1800 rating',
        'tier'        => 'a',
        'category'    => 'rating',
        'secret'      => false,
        'group'       => 'rating_milestone',
    ],
    'legendary' => [
        'name'        => 'Legendary',
        'description' => 'Reach 1900 rating',
        'tier'        => 's',
        'category'    => 'rating',
        'secret'      => false,
        'group'       => 'rating_milestone',
    ],
    'mythical' => [
        'name'        => 'Mythical',
        'description' => 'Reach 2000 rating',
        'tier'        => 's',
        'category'    => 'rating',
        'secret'      => false,
        'group'       => 'rating_milestone',
    ],
    'transcendent' => [
        'name'        => 'Transcendent',
        'description' => 'Reach 2100 rating',
        'tier'        => 's',
        'category'    => 'rating',
        'secret'      => false,
        'group'       => 'rating_milestone',
    ],
    'otherworldly' => [
        'name'        => 'Otherworldly',
        'description' => 'Reach 2200 rating',
        'tier'        => 's',
        'category'    => 'rating',
        'secret'      => false,
        'group'       => 'rating_milestone',
    ],
    'god_tier' => [
        'name'        => 'God-tier',
        'description' => 'Reach 2300+ rating',
        'tier'        => 's',
        'category'    => 'rating',
        'secret'      => false,
        'group'       => 'rating_milestone',
    ],
        'giant_slayer' => [
        'name'        => 'Giant Slayer',
        'description' => 'Beat a player rated 200+ higher than you (both players must have 30+ games)',
        'tier'        => 'a',
        'category'    => 'rating',
        'secret'      => false,
        'group'       => 'giant_slayer',
    ],
    'david_vs_goliath' => [
        'name'        => 'David vs Goliath',
        'description' => 'Beat a player rated 300+ higher than you (both players must have 30+ games)',
        'tier'        => 'a',
        'category'    => 'rating',
        'secret'      => false,
        'group'       => 'giant_slayer',
    ],
    'rocket' => [
        'name'        => 'Rocket',
        'description' => 'Gain 100+ rating points in a single month',
        'tier'        => 'b',
        'category'    => 'rating',
        'secret'      => false,
        'group'       => null,
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
    ],
    'hot_streak' => [
        'name'        => 'Hot Streak',
        'description' => 'Win 5 games in a row',
        'tier'        => 'c',
        'category'    => 'streaks',
        'secret'      => false,
        'group'       => 'win_streak',
    ],
    'rampage' => [
        'name'        => 'Rampage',
        'description' => 'Win 10 games in a row',
        'tier'        => 'b',
        'category'    => 'streaks',
        'secret'      => false,
        'group'       => 'win_streak',
    ],
    'unstoppable_streak' => [
        'name'        => 'Unstoppable',
        'description' => 'Win 15 games in a row',
        'tier'        => 'a',
        'category'    => 'streaks',
        'secret'      => false,
        'group'       => 'win_streak',
    ],
    'juggernaut' => [
        'name'        => 'Juggernaut',
        'description' => 'Win 25 games in a row',
        'tier'        => 's',
        'category'    => 'streaks',
        'secret'      => false,
        'group'       => 'win_streak',
    ],
    'terminator' => [
        'name'        => 'Terminator',
        'description' => 'Win 50 games in a row',
        'tier'        => 's',
        'category'    => 'streaks',
        'secret'      => false,
        'group'       => 'win_streak',
    ],
    'phoenix' => [
        'name'        => 'Phoenix',
        'description' => 'Return to playing after 12+ months away',
        'tier'        => 'c',
        'category'    => 'streaks',
        'secret'      => false,
        'group'       => null,
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
    ],
    'cursed' => [
        'name'        => 'Cursed',
        'description' => 'Lose to the same player 10 times',
        'tier'        => 'c',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'rivalry_losses',
    ],
    'bully' => [
        'name'        => 'Bully',
        'description' => 'Beat the same player 10 times',
        'tier'        => 'c',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'rivalry_wins',
    ],
    'executioner' => [
        'name'        => 'Executioner',
        'description' => 'Beat the same player 20 times',
        'tier'        => 'b',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'rivalry_wins',
    ],
    'the_rematch' => [
        'name'        => 'The Rematch',
        'description' => 'Play the same player 30+ times',
        'tier'        => 'b',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'rivalry_total',
    ],
    'the_rivalry' => [
        'name'        => 'The Rivalry',
        'description' => 'Play the same player 50+ times',
        'tier'        => 'a',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'rivalry_total',
    ],
    'the_feud' => [
        'name'        => 'The Feud',
        'description' => 'Play the same player 100+ times',
        'tier'        => 's',
        'category'    => 'rivalry',
        'secret'      => false,
        'group'       => 'rivalry_total',
    ],


    // -------------------------------------------------------------------------
    // Community
    // -------------------------------------------------------------------------
    'traveler' => [
        'name'        => 'First Contact',
        'description' => 'Play against players from 5 different countries',
        'tier'        => 'd',
        'category'    => 'community',
        'secret'      => false,
        'group'       => 'countries',
    ],
    'explorer' => [
        'name'        => 'Deep Space',
        'description' => 'Play against players from 10 different countries',
        'tier'        => 'c',
        'category'    => 'community',
        'secret'      => false,
        'group'       => 'countries',
    ],
    'globetrotter' => [
        'name'        => 'Sector Commander',
        'description' => 'Play against players from 20 different countries',
        'tier'        => 'b',
        'category'    => 'community',
        'secret'      => false,
        'group'       => 'countries',
    ],
    'citizen_of_the_universe' => [
        'name'        => 'Citizen of the Universe',
        'description' => 'Play against players from 30 different countries',
        'tier'        => 'a',
        'category'    => 'community',
        'secret'      => false,
        'group'       => 'countries',
    ],
    'koprulu_cartographer' => [
        'name'        => 'Koprulu Cartographer',
        'description' => 'Play against players from 40 different countries',
        'tier'        => 's',
        'category'    => 'community',
        'secret'      => false,
        'group'       => 'countries',
    ],
    
    'circuit_player' => [
        'name'        => 'Skirmish',
        'description' => 'Five battles fought.',
        'tier'        => 'd',
        'category'    => 'community',
        'secret'      => false,
        'group'       => 'tournaments',
    ],
    'road_warrior' => [
        'name'        => 'Campaign',
        'description' => 'Twenty-five battles fought.',
        'tier'        => 'c',
        'category'    => 'community',
        'secret'      => false,
        'group'       => 'tournaments',
    ],
    'legend_of_the_circuit' => [
        'name'        => 'Warlord',
        'description' => 'A hundred battles fought.',
        'tier'        => 'b',
        'category'    => 'community',
        'secret'      => false,
        'group'       => 'tournaments',
    ],
    'conqueror' => [
        'name'        => 'Conqueror',
        'description' => 'Two hundred and fifty battles fought.',
        'tier'        => 'a',
        'category'    => 'community',
        'secret'      => false,
        'group'       => 'tournaments',
    ],
    'war_boy' => [
        'name'        => 'War Boy',
        'description' => 'Five hundred battles fought. On the Fury Road.',
        'tier'        => 's',
        'category'    => 'community',
        'secret'      => false,
        'group'       => 'tournaments',
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
    ],
    'founding_father' => [
        'name'        => 'Founding Father',
        'description' => 'One of the first 50 players in the database',
        'tier'        => 'a',
        'category'    => 'history',
        'secret'      => false,
        'group'       => 'founding',
    ],
    'time_traveler' => [
        'name'        => 'Time Traveler',
        'description' => 'Have games from 3+ different years',
        'tier'        => 'd',
        'category'    => 'history',
        'secret'      => false,
        'group'       => null,
    ],
        'old_breed' => [
        'name'        => 'Old Breed',
        'description' => 'Play a game in 2017 or earlier.',
        'tier'        => 'c',
        'category'    => 'history',
        'secret'      => false,
        'group'       => null,
    ],
    'before_the_plague' => [
        'name'        => 'Before the Plague',
        'description' => 'Before the swarm arrived. Play a game before 2020.',
        'tier'        => 'c',
        'category'    => 'history',
        'secret'      => false,
        'group'       => null,
    ],
    'patient_zero' => [
        'name'        => 'Patient Zero',
        'description' => 'Playing through the plague. Play a game in 2020.',
        'tier'        => 'c',
        'category'    => 'history',
        'secret'      => false,
        'group'       => null,
    ],
    'plague_survivor' => [
        'name'        => 'Plague Survivor',
        'description' => 'The swarm could not stop you. Play a game before and after 2020.',
        'tier'        => 'a',
        'category'    => 'history',
        'secret'      => false,
        'group'       => null,
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
    ],

    'against_all_odds_10' => [
        'name'        => 'David',
        'description' => 'Beat a top 10 player while being outside top 50 (both players must have 30+ games)',
        'tier'        => 'a',
        'category'    => 'drama',
        'secret'      => false,
        'group'       => 'against_all_odds',
    ],
    'against_all_odds' => [
        'name'        => 'Against All Odds',
        'description' => 'Beat a top 3 player while being outside top 50 (both players must have 30+ games)',
        'tier'        => 's',
        'category'    => 'drama',
        'secret'      => false,
        'group'       => 'against_all_odds',
    ],
    'upset_king' => [
        'name'        => 'Upset King',
        'description' => 'Beat a player 200+ higher than you 5 times',
        'tier'        => 'a',
        'category'    => 'drama',
        'secret'      => false,
        'group'       => null,
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
    ],
    'no_days_off' => [
        'name'        => 'No Days Off',
        'description' => 'Sleep is for the weak. 10 active days in a month.',
        'tier'        => 'b',
        'category'    => 'calendar',
        'secret'      => false,
        'group'       => 'active_days',
    ],
    'workaholic' => [
        'name'        => 'Workaholic',
        'description' => 'What is a weekend? 20 active days in a month.',
        'tier'        => 's',
        'category'    => 'calendar',
        'secret'      => false,
        'group'       => 'active_days',
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
    ],
        'early_pressure' => [
        'name'        => 'Early Pressure',
        'description' => 'Deny the expansion. Win your first 3 games in a row.',
        'tier'        => 'b',
        'category'    => 'prestige',
        'secret'      => false,
        'group'       => 'perfect_start',
    ],
    'all_in' => [
        'name'        => 'All In',
        'description' => 'No going back. Win your first 5 games in a row.',
        'tier'        => 'a',
        'category'    => 'prestige',
        'secret'      => false,
        'group'       => 'perfect_start',
    ],
    'perfect_start' => [
        'name'        => 'Perfect Start',
        'description' => 'Flawless execution. Win your first 10 games in a row.',
        'tier'        => 's',
        'category'    => 'prestige',
        'secret'      => false,
        'group'       => 'perfect_start',
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
    ],
    'valentines' => [
        'name'        => "Valentine's",
        'description' => 'Play a game on February 14th',
        'tier'        => 'a',
        'category'    => 'secret',
        'secret'      => true,
        'group'       => null,
    ],
    'christmas' => [
        'name'        => 'Christmas',
        'description' => 'Play a game on December 25th',
        'tier'        => 'a',
        'category'    => 'secret',
        'secret'      => true,
        'group'       => null,
    ],
    'halloween' => [
        'name'        => 'Halloween',
        'description' => 'Play a game on October 31st',
        'tier'        => 'a',
        'category'    => 'secret',
        'secret'      => true,
        'group'       => null,
    ],
    'sc_birthday' => [
        'name'        => 'StarCraft Birthday',
        'description' => 'Play a game on March 31st — StarCraft\'s anniversary',
        'tier'        => 'a',
        'category'    => 'secret',
        'secret'      => true,
        'group'       => null,
    ],
        'bw_birthday' => [
        'name'        => 'Brood War Birthday',
        'description' => 'Play a game on November 30th — Brood War\'s anniversary',
        'tier'        => 'a',
        'category'    => 'secret',
        'secret'      => true,
        'group'       => null,
    ],
    'remastered_birthday' => [
        'name'        => 'Remastered Birthday',
        'description' => 'Play a game on August 14th — StarCraft Remastered\'s anniversary',
        'tier'        => 'a',
        'category'    => 'secret',
        'secret'      => true,
        'group'       => null,
    ],
    'mirror_match' => [
        'name'        => 'Mirror Match',
        'description' => 'Play against a player with the exact same rating as you',
        'tier'        => 'd',
        'category'    => 'secret',
        'secret'      => true,
        'group'       => null,
    ],
    'beast_mode' => [
        'name'        => 'Beast Mode',
        'description' => 'Play your 666th game',
        'tier'        => 'a',
        'category'    => 'secret',
        'secret'      => true,
        'group'       => null,
    ],
    'millennium' => [
        'name'        => 'Millennium',
        'description' => 'Play your 1000th game',
        'tier'        => 'a',
        'category'    => 'secret',
        'secret'      => true,
        'group'       => null,
    ],
    'back_to_square_one' => [
        'name'        => 'Back to Square One',
        'description' => 'End a game with exactly 1000 rating',
        'tier'        => 'c',
        'category'    => 'secret',
        'secret'      => true,
        'group'       => null,
    ],
    'bad_day' => [
        'name'        => "It's Just a Bad Day",
        'description' => 'Lose 10 games in a row',
        'tier'        => 'd',
        'category'    => 'secret',
        'secret'      => true,
        'group'       => 'loss_streak',
    ],
    'different_game' => [
        'name'        => 'Maybe Try a Different Game?',
        'description' => 'Lose 20 games in a row',
        'tier'        => 'c',
        'category'    => 'secret',
        'secret'      => true,
        'group'       => 'loss_streak',
    ],
    'dedicated_to_the_cause' => [
        'name'        => 'Dedicated to the Cause',
        'description' => 'Lose 30 games in a row',
        'tier'        => 's',
        'category'    => 'secret',
        'secret'      => true,
        'group'       => 'loss_streak',
    ],
    'how' => [
        'name'        => 'How?!',
        'description' => 'Lose to a player rated 500+ lower than you (both players must have 30+ games)',        
        'tier'        => 'd',
        'category'    => 'secret',
        'secret'      => true,
        'group'       => null,
    ],
    'rookie_mistake' => [
        'name'        => 'Rookie Mistake',
        'description' => 'Lose your very first ranked game',
        'tier'        => 'd',
        'category'    => 'secret',
        'secret'      => true,
        'group'       => null,
    ],

];