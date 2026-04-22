<?php

return [
    'url' => rtrim((string) env('POCKETBASE_URL'), '/'),
    'superuser_email' => env('POCKETBASE_SUPERUSER_EMAIL'),
    'superuser_password' => env('POCKETBASE_SUPERUSER_PASSWORD'),
    'timeout' => (int) env('POCKETBASE_TIMEOUT', 15),
];
