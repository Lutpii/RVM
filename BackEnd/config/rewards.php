<?php
// BackEnd/config/rewards.php
return [
    // Filename only (not a full path): RewardConfigService always resolves it
    // under storage_path('app/'). Overridden in phpunit.xml so the test suite
    // never reads or writes the real, admin-configured reward_config.json.
    'config_filename' => env('REWARD_CONFIG_FILENAME', 'reward_config.json'),
];
