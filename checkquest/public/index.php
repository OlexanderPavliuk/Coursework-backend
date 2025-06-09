<?php
// public/index.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__.'/../core/Response.php';

ob_start();

spl_autoload_register(function ($class) {
    $path = str_replace('\\', '/', $class) . '.php';
    $file = __DIR__ . '/../' . $path;
    if (file_exists($file)) {
        require $file;
    }
});

use Core\App;

$router = require __DIR__ . '/../config/routes.php';
$router->middleware('auth', function () {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }
});

$app = new App($router);
$app->run();

ob_end_flush();