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
$router->delete('/app/projects/{id}', [ProjectController::class, 'delete']);
$router->post('/app/projects/{projectId}/members', [ProjectController::class, 'addMembers']);
$router->get('/app/projects/{projectId}/{documentId}', [ProjectController::class, 'showDocument']);
$router->post('/app/projects/{projectId}/{documentId}/threads', [ProjectController::class, 'createReviewThread']);
$router->post('/app/projects/{projectId}/{documentId}/threads/{threadId}/comments', [ProjectController::class, 'addReviewComment']);
$router->post('/app/projects/{projectId}/{documentId}/threads/{threadId}/resolve', [ProjectController::class, 'resolveReviewThread']);
$router->get('/app/documents/file/{versionId}', [ProjectController::class, 'streamDocumentVersion']);
$router->post('/app/projects/{projectId}/documents', [ProjectController::class, 'uploadDocument']);
$router->post('/app/projects/{projectId}/{documentId}/versions', [ProjectController::class, 'uploadDocumentVersion']);
$router->post('/app/projects/{projectId}/{documentId}/approve', [ProjectController::class, 'approveDocumentVersion']);
$router->post('/app/projects/{projectId}/members/{memberUserId}/role', [ProjectController::class, 'updateMemberRole']);
$router->post('/app/projects/{projectId}/members/{memberUserId}/remove', [ProjectController::class, 'removeMember']);
$router->get('/app/notifications', [ProjectController::class, 'getNotifications']);
$router->post('/app/notifications/{notificationId}/read', [ProjectController::class, 'markNotificationRead']);
$router->post('/app/notifications/read-all', [ProjectController::class, 'markAllNotificationsRead']);
$router->get('/app/invitations', [ProjectController::class, 'getInvitations']);
$router->post('/app/invitations/{invitationId}/accept', [ProjectController::class, 'acceptInvitation']);
$router->post('/app/invitations/{invitationId}/decline', [ProjectController::class, 'declineInvitation']);
$router->get('/app/users/search', [ProjectController::class, 'searchUsers']);
$router->post('/app/projects', [ProjectController::class, 'create']);
$router->get('/dashboard', [HomeController::class, 'dashboard']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/logout', [AuthController::class, 'logout']);

$requestMethod = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
if ($requestMethod === 'POST') {
    $spoofedMethod = strtoupper((string) ($_POST['_method'] ?? ''));
    if (in_array($spoofedMethod, ['DELETE'], true)) {
        $requestMethod = $spoofedMethod;
    }
}

$router->dispatch($requestMethod, $requestedPath);
