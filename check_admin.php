<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('email', 'admin@gmail.com')->first();
if ($user) {
    echo 'Email: ' . $user->email . PHP_EOL;
    echo 'Role ID: ' . $user->role_id . PHP_EOL;
    echo 'Role Name: ' . ($user->role ? $user->role->name : 'NULL') . PHP_EOL;
} else {
    echo 'Admin user not found.' . PHP_EOL;
}
