<?php

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::query()->where('email', 'admin@tap.local')->first();

try {
    $token = $user->createToken('api');
    echo 'OK:'.substr($token->plainTextToken, 0, 24).PHP_EOL;
} catch (Throwable $e) {
    echo 'ERR:'.$e->getMessage().PHP_EOL;
}
