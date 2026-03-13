<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\ProjectController;
use App\Core\Router;

const BASE_PATH = __DIR__ . '/..';

session_start();

$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
$scriptDir = $scriptDir === '.' ? '' : rtrim($scriptDir, '/');
define('BASE_URL', $scriptDir === '' ? '' : $scriptDir);

spl_autoload_register(static function (string $className): void {
    $prefix = 'App\\';

    if (strncmp($className, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($className, strlen($prefix));
    $filePath = BASE_PATH . '/app/' . str_replace('\\', '/', $relativeClass) . '.php';

    if (is_file($filePath)) {
        require $filePath;
    }
});

require BASE_PATH . '/app/Core/helpers.php';

$requestedPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (BASE_URL !== '' && str_starts_with($requestedPath, BASE_URL)) {
    $requestedPath = substr($requestedPath, strlen(BASE_URL));
}
$requestedPath = $requestedPath === '' ? '/' : $requestedPath;

$router = new Router();
$router->get('/', [HomeController::class, 'index']);
$router->get('/app', [HomeController::class, 'appDashboard']);
$router->get('/app/projects/{id}', [ProjectController::class, 'show']);
$router->get('/app/users/search', [ProjectController::class, 'searchUsers']);
$router->post('/app/projects', [ProjectController::class, 'create']);
$router->get('/dashboard', [HomeController::class, 'dashboard']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/logout', [AuthController::class, 'logout']);

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $requestedPath);
