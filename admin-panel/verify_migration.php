<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::whereNotNull('role_id')->first();
if ($user) {
    echo 'User: ' . $user->name . ' | Role ID: ' . $user->role_id . ' | Role Name: ' . ($user->role ? $user->role->name : 'MISSING RELATION') . PHP_EOL;
} else {
    echo 'No user with role_id found.' . PHP_EOL;
}
