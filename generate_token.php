<?php

use App\Models\User;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

// Find the first user or create one if none exist
$user = User::first();

if (!$user) {
  echo "No users found. Creating a default user...\n";
  $user = User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('password'),
  ]);
}

// Create token
$token = $user->createToken('MakeIntegration')->plainTextToken;

echo "TOKEN_START\n";
echo $token . "\n";
echo "TOKEN_END\n";
