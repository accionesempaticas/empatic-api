<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $laravelVersion = Illuminate\Foundation\Application::VERSION;
    $phpVersion = PHP_VERSION;

    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Laravel</title>
    <style>
        body { font-family: sans-serif; background-color: #f7fafc; color: #2d3748; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .container { text-align: center; padding: 2rem; background-color: white; border-radius: 0.5rem; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
        h1 { font-size: 2.25rem; }
        p { color: #718096; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Laravel</h1>
        <p>Application is running.</p>
        <p><small>Laravel v{$laravelVersion} (PHP v{$phpVersion})</small></p>
    </div>
</body>
</html>
HTML;
});