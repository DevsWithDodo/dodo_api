<?php

return [
    'google' => [
        'client_id' => env('GOOGLE_OAUTH_CLIENT_ID'),
    ],
    'apple' => [
        'service_id' => env('APPLE_OAUTH_SERVICE_ID'),
        'redirect_uri' => env('APPLE_OAUTH_REDIRECT_URI'),
        'team_id' => env('APPLE_TEAM_ID'),
        'key_id' => env('APPLE_OAUTH_KEY_ID'),
        'private_key' => env('APPLE_OAUTH_PRIVATE_KEY'),
        'bundle_id' => env('IOS_BUNDLE_ID'),
        'android_package_name' => env('ANDROID_PACKAGE_NAME'),
    ]
];