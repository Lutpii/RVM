<?php

return [
    'enabled' => (bool) env('PLATFORM_MODE', false),
    'booth_email' => env('PLATFORM_BOOTH_EMAIL') ?: null,
];
