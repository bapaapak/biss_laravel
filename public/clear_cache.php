<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Clear caches
Artisan::call('config:clear');
echo "✅ Config cleared<br>";

Artisan::call('cache:clear');
echo "✅ Cache cleared<br>";

Artisan::call('view:clear');
echo "✅ View cleared<br>";

Artisan::call('route:clear');
echo "✅ Route cleared<br>";

echo "<br><strong>🎉 All caches cleared!</strong>";