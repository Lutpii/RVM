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

    // Python AI Service
    'ai' => [
        'url' => env('AI_SERVICE_URL', 'http://localhost:5000'),
        'key' => env('AI_SERVICE_KEY', 'rvm_ai_secret_key_2024'),
    ],

    // Vue Frontend URL (used to build QR scan links)
    'frontend_url' => env('FRONTEND_URL', 'http://localhost:5173'),
];
