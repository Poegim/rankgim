<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Inactive Player Threshold
    |--------------------------------------------------------------------------
    |
    | Number of months without a game after which a player is considered
    | inactive and excluded from the public ranking and country stats.
    |
    | This value is used in:
    |   - app/Livewire/Rankings/Index.php
    |   - app/Livewire/Dashboard.php
    |   - app/Services/EloService.php    (buildSnapshot)
    |   - app/Services/StatsService.php  (buildCountryStats)
    |   - resources/views/about.blade.php (displayed to the user)
    |
    | After changing this value, run a full recalculation:
    |   sail artisan app:recalculate-elo
    |
    */
    'inactive_months' => 6,

];