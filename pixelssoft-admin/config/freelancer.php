<?php

return [
    /*
    | Freelancer.com official OAuth + API (see https://developers.freelancer.com/)
    | Sandbox uses accounts.freelancer-sandbox.com and www.freelancer-sandbox.com
    */

    'client_id' => env('FREELANCER_CLIENT_ID'),
    'client_secret' => env('FREELANCER_CLIENT_SECRET'),
    'redirect_uri' => env('FREELANCER_REDIRECT_URI', env('APP_URL').'/admin/freelancer/oauth/callback'),

    'sandbox' => (bool) env('FREELANCER_SANDBOX', true),

    'timezone' => env('FREELANCER_TIMEZONE', 'Asia/Karachi'),

    'oauth' => [
        'authorize_url' => env(
            'FREELANCER_OAUTH_AUTHORIZE_URL',
            env('FREELANCER_SANDBOX', true)
                ? 'https://accounts.freelancer-sandbox.com/oauth/authorise'
                : 'https://accounts.freelancer.com/oauth/authorise'
        ),
        'token_url' => env(
            'FREELANCER_OAUTH_TOKEN_URL',
            env('FREELANCER_SANDBOX', true)
                ? 'https://accounts.freelancer-sandbox.com/oauth/token'
                : 'https://accounts.freelancer.com/oauth/token'
        ),
        // Official scopes; advanced scopes requested via advanced_scopes query param.
        // Keep FREELANCER_ADVANCED_SCOPES as small as your app needs (see Freelancer OAuth demo).
        'scope' => env('FREELANCER_OAUTH_SCOPE', 'basic'),
        'advanced_scopes' => env('FREELANCER_ADVANCED_SCOPES', '1 2 3 4 5'),
        'prompt' => 'select_account consent',
    ],

    'api' => [
        'base_url' => env(
            'FREELANCER_API_BASE_URL',
            env('FREELANCER_SANDBOX', true)
                ? 'https://www.freelancer-sandbox.com/api/'
                : 'https://www.freelancer.com/api/'
        ),
        'timeout' => (int) env('FREELANCER_API_TIMEOUT', 30),
        'retries' => (int) env('FREELANCER_API_RETRIES', 2),
        'retry_sleep_ms' => (int) env('FREELANCER_API_RETRY_SLEEP_MS', 500),
    ],

    'sync' => [
        'projects_interval_minutes' => (int) env('FREELANCER_SYNC_PROJECTS_MINUTES', 5),
        'profile_interval_minutes' => (int) env('FREELANCER_SYNC_PROFILE_MINUTES', 60),
    ],

    'defaults' => [
        'dry_run' => (bool) env('FREELANCER_DRY_RUN', true),
        'global_paused' => (bool) env('FREELANCER_GLOBAL_PAUSED', false),
        'daily_bid_limit' => (int) env('FREELANCER_DAILY_BID_LIMIT', 20),
        'hourly_bid_limit' => (int) env('FREELANCER_HOURLY_BID_LIMIT', 5),
        'monthly_bid_limit' => (int) env('FREELANCER_MONTHLY_BID_LIMIT', 500),
        'min_skill_match_percent' => (int) env('FREELANCER_MIN_SKILL_MATCH', 60),
        'max_project_age_minutes' => (int) env('FREELANCER_MAX_PROJECT_AGE_MINUTES', 15),
        'bid_delay_seconds' => (int) env('FREELANCER_BID_DELAY_SECONDS', 60),
    ],

    'score_weights' => [
        'skill' => 40,
        'keyword' => 20,
        'budget' => 15,
        'country' => 10,
        'client' => 10,
        'freshness' => 5,
    ],
];
