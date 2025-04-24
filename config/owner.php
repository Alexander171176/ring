<?php

return [
    'name' => 'Александр Косолапов',
    'email' => env('OWNER_NOTIFY_EMAIL', 'kosolapov1976@gmail.com'),
    'default_password' => env('OWNER_DEFAULT_PASSWORD', 'secure_default_password'),
    'system_user_id' => 1,
    'notify_email' => 'kosolapov1976@gmail.com',
];

// config('owner.email')
// config('owner.default_password')
// config('owner.system_user_id')

// 'user_id' => config('owner.system_user_id'),
// 'password' => Hash::make(config('owner.default_password')),
// Mail::to(config('owner.email'))->send(...)
