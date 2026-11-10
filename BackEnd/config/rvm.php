<?php

return [
    // When true, every /api/hardware/* call must carry a valid X-Machine-Key.
    // Keep false until each kiosk has been given its key (php artisan
    // rvm:machine-key <code> + VITE_MACHINE_KEY in the kiosk build), otherwise
    // the kiosks lose access to their own hardware routes.
    'machine_auth_enforce' => (bool) env('MACHINE_AUTH_ENFORCE', false),
];
