<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [],

    // The frontend is reachable at localhost during dev AND at whatever LAN IP
    // a kiosk's own wifi hands it (e.g. https://10.27.26.214:5173 — see
    // FRONTEND_URL in .env), so an exact allowlist would be too fragile; this
    // pattern restricts to "localhost or an RFC1918 private address, on one of
    // the two known Vite ports" — excludes any public/external origin like
    // https://evil-attacker.example while still covering real deployments.
    'allowed_origins_patterns' => [
        '#^https://(localhost|10\.\d{1,3}\.\d{1,3}\.\d{1,3}|192\.168\.\d{1,3}\.\d{1,3}|172\.(1[6-9]|2\d|3[01])\.\d{1,3}\.\d{1,3}):(5173|4173)$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
