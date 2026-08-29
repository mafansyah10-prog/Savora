<?php
use Illuminate\Support\Facades\Artisan;

define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(
    Illuminate\Http\Request::capture()
);

try {
    echo "Running migration...<br>";
    $exitCode = Artisan::call('migrate', ['--force' => true]);
    echo "Artisan call completed with exit code: " . $exitCode . "<br>";
    echo "<pre>" . Artisan::output() . "</pre>";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
