<?php

return [
    'route_prefix' => env('LOANENGINE_AUTH_ROUTE_PREFIX', ''),
    'redirect_after_login' => '/dashboard',
    'redirect_after_register' => '/dashboard',
    'registration_enabled' => true,
    'password_min_length' => 10,
    'name_max_length' => 120,
    'token_name' => 'loanengine-api',
    'token_abilities' => ['*'],
    'token_expiration_minutes' => (int) env('SANCTUM_TOKEN_EXPIRATION', 10080),
    'theme' => [
        'page' => '#F3F4F6',
        'surface' => '#FFFFFF',
        'text' => '#171717',
        'muted' => '#6B7280',
        'border' => '#D1D5DB',
        'primary' => '#171717',
        'primary_hover' => '#333333',
        'primary_text' => '#FFFFFF',
        'input_background' => '#FFFFFF',
        'focus_ring' => '#737373',
        'danger' => '#B91C1C',
        'success' => '#166534',
        'radius' => '14px',
        'font_family' => 'Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
    ],
];
