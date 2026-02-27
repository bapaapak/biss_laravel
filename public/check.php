<?php
echo "<h2>Laravel Structure Check</h2>";

$base = dirname(__DIR__);
$checks = [
    'bootstrap/app.php',
    'config/app.php',
    'vendor/autoload.php',
    'vendor/laravel/framework/src/Illuminate/View/ViewServiceProvider.php',
    'resources/views',
    '.env',
    'routes/web.php',
    'app/Providers/AppServiceProvider.php',
];

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>File/Folder</th><th>Status</th></tr>";

foreach ($checks as $path) {
    $fullPath = $base . '/' . $path;
    $exists = file_exists($fullPath);
    $status = $exists ? "✅ EXISTS" : "❌ NOT FOUND";
    $color = $exists ? "green" : "red";
    echo "<tr><td>$path</td><td style='color:$color'>$status</td></tr>";
}
echo "</table>";

// Check .env content
echo "<h3>.env Check:</h3>";
$envPath = $base . '/.env';
if (file_exists($envPath)) {
    $env = file_get_contents($envPath);
    $hasAppKey = strpos($env, 'APP_KEY=base64:') !== false;
    echo $hasAppKey ? "✅ APP_KEY exists" : "❌ APP_KEY missing";
} else {
    echo "❌ .env file not found";
}