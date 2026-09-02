<?php
// config/services.php
return [
    'mailgun'  => ['domain' => env('MAILGUN_DOMAIN'), 'secret' => env('MAILGUN_SECRET'), 'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net')],
    'postmark' => ['token' => env('POSTMARK_TOKEN')],
    'ses'      => ['key' => env('AWS_ACCESS_KEY_ID'), 'secret' => env('AWS_SECRET_ACCESS_KEY'), 'region' => env('AWS_DEFAULT_REGION', 'us-east-1')],

    // Google OAuth
    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URI'),
    ],

    // Fonnte WhatsApp
    'fonnte' => [
        'token'  => env('FONNTE_API_TOKEN'),
        'sender' => env('FONNTE_SENDER'),
    ],

    // Python AI Service — no fallback default: a missing key must fail loudly,
    // not silently resolve to a value that's public knowledge from the repo.
    'ai' => [
        'url' => env('AI_SERVICE_URL', 'http://localhost:5000'),
        'key' => env('AI_SERVICE_KEY'),
    ],

    // Vue Frontend URL (used to build QR scan links)
    'frontend_url' => env('FRONTEND_URL', 'http://localhost:5173'),

    // Port `php artisan serve` actually listens on — used to build a
    // caller-reachable backend URL for the Google OAuth start/callback hops,
    // which must be reached directly (not through the Vite proxy). See
    // AuthController::rootForHost().
    'backend_port' => env('BACKEND_PORT', 8000),

    // Explicit override for the backend's Google-OAuth-reachable address —
    // e.g. an ngrok tunnel (https://xxx.ngrok-free.dev). Takes priority over
    // both $request->root() and the host-query-param inference, because
    // Google flatly rejects a redirect_uri on a private IP (192.168.x.x etc)
    // with "device_id and device_name are required for private IP" — no
    // code-side fix for that; a public HTTPS tunnel is the only way to test
    // Google login from a phone that isn't on localhost. Leave unset to fall
    // back to the previous behavior (localhost / direct LAN IP, no tunnel).
    'public_backend_url' => env('PUBLIC_BACKEND_URL'),
];
