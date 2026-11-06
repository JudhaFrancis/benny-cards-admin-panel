<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$roles = \App\Models\Role::all();
foreach ($roles as $role) {
    echo $role->id . ': ' . $role->name . PHP_EOL;
}
