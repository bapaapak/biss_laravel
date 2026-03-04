<?php
// Standalone deploy script - bypasses Laravel routing/cache
$key = $_GET['key'] ?? '';
if ($key !== 'bis-deploy-2026') {
    http_response_code(403);
    die('Unauthorized');
}

header('Content-Type: text/plain');
$output = '';

// 1. Git pull
$output .= "=== Git Pull ===\n";
$gitOutput = shell_exec('cd ' . dirname(__DIR__) . ' && git fetch origin main 2>&1 && git reset --hard origin/main 2>&1');
$output .= $gitOutput . "\n";

// 2. Clear opcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    $output .= "OPcache cleared.\n";
}

// 3. Artisan commands with individual error handling
$basePath = dirname(__DIR__);
$php = 'php'; // adjust if needed

// Try config:clear
$output .= "=== Config Clear ===\n";
$output .= shell_exec("cd $basePath && $php artisan config:clear 2>&1") . "\n";

// Try route:clear
$output .= "=== Route Clear ===\n";
$output .= shell_exec("cd $basePath && $php artisan route:clear 2>&1") . "\n";

// Try view:clear
$output .= "=== View Clear ===\n";
$output .= shell_exec("cd $basePath && $php artisan view:clear 2>&1") . "\n";

// Run migrations
$output .= "=== Migrate ===\n";
$output .= shell_exec("cd $basePath && $php artisan migrate --force 2>&1") . "\n";

echo $output;
echo "\n=== Deploy Complete ===\n";
