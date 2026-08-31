<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$apiKey = env('GEMINI_API_KEY');

$models = [
    'gemini-2.5-flash',
    'gemini-3.5-flash',
    'gemini-3.6-flash',
    'gemini-3.7-flash',
];

foreach ($models as $model) {
    $url = "https://generativelanguage.googleapis.com/v1/models/{$model}:generateContent?key=" . $apiKey;
    $start = microtime(true);
    $response = Http::timeout(10)->post($url, [
        'contents' => [
            ['parts' => [['text' => 'Hello']]]
        ]
    ]);
    $duration = round(microtime(true) - $start, 2);
    echo "Model: {$model} | Status: " . $response->status() . " | Time: {$duration}s\n";
    if (!$response->successful()) {
        echo "  Error: " . $response->body() . "\n";
    }
}
