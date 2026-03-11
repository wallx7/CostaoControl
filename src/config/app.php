<?php
require_once __DIR__ . '/config.php';

return [
    'auth' => [
        'provider' => 'banco',
    ],
    'app' => [
        'name' => config('APP_NAME', 'Inventário de TI'),
        'url' => config('APP_URL', 'http://localhost:8000'),
    ],
];
