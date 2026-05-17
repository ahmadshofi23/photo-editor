<?php

return [
    'uploads' => [
        'max_size_kb' => env('SECURITY_MAX_UPLOAD_SIZE', 10240), // 10MB
        'max_per_day' => env('SECURITY_MAX_UPLOADS_PER_DAY', 50),
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
        'malicious_extensions' => ['php', 'phtml', 'exe', 'sh', 'bat', 'js'],
    ],
    'rate_limits' => [
        'api_upload' => 60,
        'api_editor' => 120,
    ]
];
