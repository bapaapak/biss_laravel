<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Path ke folder biss di: /home/ssotoght/biss/
// Document root subdomain: /home/ssotoght/biss.tubagus.my.id/

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../biss/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../biss/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
$app = require_once __DIR__.'/../biss/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
