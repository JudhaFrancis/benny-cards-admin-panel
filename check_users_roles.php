<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = \App\Models\User::with('role')->get();
foreach ($users as $user) {
    echo "User ID: " . $user->id . ", Name: " . $user->name . ", Role: " . ($user->role ? $user->role->name : 'NONE') . PHP_EOL;
}
