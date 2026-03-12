<?php

return [
    'temp_secret' => env('TEMP_JWT_SECRET', env('APP_KEY')),
    'temp_ttl' => env('TEMP_JWT_TTL', 10),
];
