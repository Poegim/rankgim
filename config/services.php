<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'soop' => [
        // SOOP Open API credentials issued via developer portal partnership.
        'client_id'     => env('SOOP_CLIENT_ID'),
        'client_secret' => env('SOOP_CLIENT_SECRET'),

        // Base URL for SOOP Open API. Override only for testing/staging.
        'base_url'      => env('SOOP_BASE_URL', 'https://openapi.sooplive.com'),

        // SOOP category number for StarCraft: Brood War.
        // Verify by calling broad/category/list once and looking for "스타크래프트".
        'bw_category'   => env('SOOP_BW_CATEGORY', '00040044'),

        // HTTP timeout in seconds for SOOP API calls.
        'timeout'       => (int) env('SOOP_TIMEOUT', 8),

        // Identifies our app to SOOP. Helpful if SOOP support ever needs to find us.
        'user_agent'    => env('SOOP_USER_AGENT', 'Rankgim/1.0 (+https://rankgim.com)'),
    ],

    'twitch' => [
        'client_id'     => env('TWITCH_CLIENT_ID'),
        'client_secret' => env('TWITCH_CLIENT_SECRET'),

        // Helix API base for streams/games/users endpoints.
        'base_url'      => env('TWITCH_BASE_URL', 'https://api.twitch.tv/helix'),

        // OAuth token endpoint — we use the client_credentials grant to mint
        // app access tokens (no user login required, lasts ~60 days).
        'oauth_url'     => env('TWITCH_OAUTH_URL', 'https://id.twitch.tv/oauth2'),

        // Twitch game IDs are numeric strings assigned by Twitch.
        //   StarCraft: Brood War  = 490634
        //   StarCraft II          = 32959  (not used currently)
        // Verify with: GET /helix/games?name=StarCraft:%20Brood%20War
        // — if Twitch ever merges/renames the category, this ID must be updated.
        'bw_game_id'    => env('TWITCH_BW_GAME_ID', '490634'),

        'timeout'       => (int) env('TWITCH_TIMEOUT', 8),
        'user_agent'    => env('TWITCH_USER_AGENT', 'Rankgim/1.0 (+https://rankgim.com)'),
    ],

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
