<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\DocumentModel;
use App\Models\NotificationModel;
use App\Models\ProjectModel;
use App\Repositories\DocumentRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\ProjectRepository;
use App\Repositories\UserRepository;

final class ProjectController extends Controller
{
    private ProjectModel $projectModel;
    private DocumentModel $documentModel;
    private NotificationModel $notificationModel;

    public function __construct()
    {
        $notificationRepo = new NotificationRepository();
        $projectRepo = new ProjectRepository();
        $this->projectModel = new ProjectModel($projectRepo, new UserRepository(), $notificationRepo);
        $this->documentModel = new DocumentModel(new DocumentRepository(), $projectRepo, $notificationRepo);
        $this->notificationModel = new NotificationModel($notificationRepo, $projectRepo);
    }

    public function searchUsers(): void
    {
        $user = Auth::user();
        if ($user === null) {
            $this->json(['ok' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $query = trim((string) ($_GET['q'] ?? ''));
        $users = $this->projectModel->searchUsers($query, (int) $user['id']);

        $this->json(['ok' => true, 'users' => $users]);
    }

    public function create(): void
    {
        $user = Auth::user();
        if ($user === null) {
            $this->json(['ok' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $payload = $this->readPayload();
        $title = trim((string) ($payload['title'] ?? ''));
        $description = trim((string) ($payload['description'] ?? ''));
        $members = $payload['members'] ?? [];

        if (!is_array($members)) {
            $this->json(['ok' => false, 'message' => 'Invalid members payload.'], 422);
            return;
        }

        $result = $this->projectModel->createProject((int) $user['id'], $title, $description, $members);

        if (!$result['ok']) {
            $this->json($result, 422);
            return;
        }

        $this->flash('success', $result['message']);
        $this->json($result, 201);
    }

    public function delete(string $projectId): void
    {
        $user = Auth::user();
        if ($user === null) {
            $this->flash('error', 'Please sign in to continue.');
            $this->redirect('/login');
        }

        $result = $this->projectModel->deleteProject((int) $projectId, (int) $user['id']);
        $this->flash($result['ok'] ? 'success' : 'error', $result['message']);
        $this->redirect('/app');
    }

    public function show(string $projectId): void
    {
        $user = Auth::user();
        if ($user === null) {
            $this->flash('error', 'Please sign in to continue.');
            $this->redirect('/login');
        }

        $id = (int) $projectId;
        if ($id <= 0) {
            $this->notFound();
            return;
        }

        $project = $this->projectModel->fetchProjectDetailForUser($id, (int) $user['id']);
        if ($project === null) {
            $this->notFound();
            return;
        }

        $members = $this->projectModel->fetchProjectMembersForUser($id, (int) $user['id']);
        $documents = $this->documentModel->fetchProjectDocumentsForUser($id, (int) $user['id']);
        $notifications = $this->notificationModel->fetchNotifications((int) $user['id']);
        $notificationUnreadCount = $this->notificationModel->getUnreadCount((int) $user['id']);
        $pendingInvitations = $this->projectModel->fetchPendingInvitations((int) $user['id']);
        $pendingInvitationCount = $this->projectModel->getPendingInvitationCount((int) $user['id']);

        $this->render('app/projects/show', [
            'user' => $user,
            'project' => $project,
            'members' => $members,
            'documents' => $documents,
            'notifications' => $notifications,
            'notificationUnreadCount' => $notificationUnreadCount,
            'pendingInvitations' => $pendingInvitations,
            'pendingInvitationCount' => $pendingInvitationCount,
        ], (string) $project['title']);
    }

    public function uploadDocument(string $projectId): void
    {
        $user = Auth::user();
        if ($user === null) {
            $this->json(['ok' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $projectIdInt = (int) $projectId;
        $title = trim((string) ($_POST['title'] ?? ''));
        $file = $_FILES['document_file'] ?? null;

        if (!is_array($file)) {
            $this->json(['ok' => false, 'message' => 'Please choose a file to upload.'], 422);
            return;
        }

        $result = $this->documentModel->uploadInitialDocument(
            $projectIdInt,
            (int) $user['id'],
            $title,
            $file
        );

        $statusCode = $result['ok'] ? 201 : 422;
        $this->json($result, $statusCode);
    }

    public function uploadDocumentVersion(string $projectId, string $documentId): void
    {
        $user = Auth::user();
        if ($user === null) {
            $this->json(['ok' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $file = $_FILES['version_file'] ?? null;
        if (!is_array($file)) {
            $this->json(['ok' => false, 'message' => 'Please choose a file to upload.'], 422);
            return;
        }

        $reviewThreadIds = $_POST['review_thread_ids'] ?? [];
        if (!is_array($reviewThreadIds)) {
            $reviewThreadIds = [];
        }

        $result = $this->documentModel->uploadNewDocumentVersion(
            (int) $projectId,
            (int) $documentId,
            (int) $user['id'],
            (int) ($_POST['base_version_id'] ?? 0),
            trim((string) ($_POST['change_summary'] ?? '')),
            $file,
            $reviewThreadIds
        );

        $statusCode = $result['ok'] ? 201 : 422;
        $this->json($result, $statusCode);
    }

    public function showDocument(string $projectId, string $documentId): void
    {
        $user = Auth::user();
        if ($user === null) {
            $this->flash('error', 'Please sign in to continue.');
            $this->redirect('/login');
        }

        $projectIdInt = (int) $projectId;
        $documentIdInt = (int) $documentId;
        if ($projectIdInt <= 0 || $documentIdInt <= 0) {
            $this->notFound();
            return;
        }

        $requestedVersion = isset($_GET['version']) ? (int) $_GET['version'] : null;
        $payload = $this->documentModel->fetchDocumentDetailForUser(
            $projectIdInt,
            $documentIdInt,
            (int) $user['id'],
            $requestedVersion
        );

        if ($payload === null) {
            $this->notFound();
            return;
        }

        $this->render('app/documents/show', [
            'user' => $user,
            'document' => $payload['document'],
            'selectedVersion' => $payload['selectedVersion'],
            'versions' => $payload['versions'],
            'threads' => $payload['threads'],
        ], (string) $payload['document']['title']);
    }

    public function createReviewThread(string $projectId, string $documentId): void
    {
        $user = Auth::user();
        if ($user === null) {
            $this->json(['ok' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $payload = $this->readPayload();
        $title = trim((string) ($payload['title'] ?? ''));
        $comment = trim((string) ($payload['comment'] ?? ''));
        $pageNumber = (int) ($payload['page_number'] ?? 0);
        $versionId = (int) ($payload['version_id'] ?? 0);

        $result = $this->documentModel->createReviewThreadForUser(
            (int) $projectId,
            (int) $documentId,
            (int) $user['id'],
            $title,
            $comment,
            $pageNumber,
            $versionId
        );

        $statusCode = $result['ok'] ? 201 : 422;
        $this->json($result, $statusCode);
    }

    public function addReviewComment(string $projectId, string $documentId, string $threadId): void
    {
        $user = Auth::user();
        if ($user === null) {
            $this->json(['ok' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $payload = $this->readPayload();
        $comment = trim((string) ($payload['comment'] ?? ''));
        $pageNumber = (int) ($payload['page_number'] ?? 0);
        $versionId = (int) ($payload['version_id'] ?? 0);

        $result = $this->documentModel->addReviewCommentForUser(
            (int) $projectId,
            (int) $documentId,
            (int) $threadId,
            (int) $user['id'],
            $comment,
            $pageNumber,
            $versionId
        );

        $statusCode = $result['ok'] ? 201 : 422;
        $this->json($result, $statusCode);
    }

    public function resolveReviewThread(string $projectId, string $documentId, string $threadId): void
    {
        $user = Auth::user();
        if ($user === null) {
            $this->json(['ok' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $payload = $this->readPayload();
        $versionId = (int) ($payload['version_id'] ?? 0);

        $result = $this->documentModel->resolveReviewThreadForUser(
            (int) $projectId,
            (int) $documentId,
            (int) $threadId,
            (int) $user['id'],
            $versionId
        );

        $statusCode = $result['ok'] ? 200 : 422;
        $this->json($result, $statusCode);
    }

    public function approveDocumentVersion(string $projectId, string $documentId): void
    {
        $user = Auth::user();
        if ($user === null) {
            $this->json(['ok' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $payload = $this->readPayload();
        $versionId = (int) ($payload['version_id'] ?? 0);

        $result = $this->documentModel->approveDocumentVersionForUser(
            (int) $projectId,
            (int) $documentId,
            (int) $user['id'],
            $versionId
        );
        $logFile = __DIR__ . '/debug.log';
        file_put_contents($logFile, print_r($payload, true) . PHP_EOL, FILE_APPEND);
        $statusCode = $result['ok'] ? 200 : 422;
        $this->json($result, $statusCode);
    }

    public function streamDocumentVersion(string $versionId): void
    {
        $user = Auth::user();
        if ($user === null) {
            http_response_code(401);
            echo 'Unauthorized';
            return;
        }

        $versionIdInt = (int) $versionId;
        $file = $this->documentModel->getVersionFileForUser($versionIdInt, (int) $user['id']);

        if ($file === null) {
            http_response_code(404);
            echo 'File not found';
            return;
        }

        $contentType = $file['fileType'] === 'docx'
            ? 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            : 'application/pdf';

        $disposition = (string) ($_GET['download'] ?? '') === '1' ? 'attachment' : 'inline';

        header('Content-Type: ' . $contentType);
        header('Content-Length: ' . (string) filesize($file['absolutePath']));
        header('Content-Disposition: ' . $disposition . '; filename="' . $file['fileName'] . '"');
        readfile($file['absolutePath']);
        exit;
    }

    public function updateMemberRole(string $projectId, string $memberUserId): void
    {
        $user = Auth::user();
        if ($user === null) {
            $this->json(['ok' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $projectIdInt = (int) $projectId;
        $memberUserIdInt = (int) $memberUserId;
        $payload = $this->readPayload();
        $role = trim((string) ($payload['role'] ?? ''));

        $result = $this->projectModel->changeMemberRole(
            $projectIdInt,
            (int) $user['id'],
            $memberUserIdInt,
            $role
        );

        $statusCode = $result['ok'] ? 200 : 422;
        $this->json($result, $statusCode);
    }

    public function addMembers(string $projectId): void
    {
        $user = Auth::user();
        if ($user === null) {
            $this->json(['ok' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $payload = $this->readPayload();
        $members = $payload['members'] ?? [];

        if (!is_array($members)) {
            $this->json(['ok' => false, 'message' => 'Invalid members payload.'], 422);
            return;
        }

        $result = $this->projectModel->inviteMembersToProject(
            (int) $projectId,
            (int) $user['id'],
            $members
        );

        $statusCode = $result['ok'] ? 201 : 422;
        $this->json($result, $statusCode);
    }

    public function removeMember(string $projectId, string $memberUserId): void
    {
        $user = Auth::user();
        if ($user === null) {
            $this->json(['ok' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $result = $this->projectModel->removeMemberFromProject(
            (int) $projectId,
            (int) $user['id'],
            (int) $memberUserId
        );

        $statusCode = $result['ok'] ? 200 : 422;
        $this->json($result, $statusCode);
    }

    public function getInvitations(): void
    {
        $user = Auth::user();
        if ($user === null) {
            $this->json(['ok' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $invitations = $this->projectModel->fetchPendingInvitations((int) $user['id']);
        $this->json(['ok' => true, 'invitations' => $invitations]);
    }

    public function acceptInvitation(string $invitationId): void
    {
        $user = Auth::user();
        if ($user === null) {
            $this->json(['ok' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $result = $this->projectModel->acceptInvitation((int) $user['id'], (int) $invitationId);
        $statusCode = $result['ok'] ? 200 : 422;
        $this->json($result, $statusCode);
    }

    public function declineInvitation(string $invitationId): void
    {
        $user = Auth::user();
        if ($user === null) {
            $this->json(['ok' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $result = $this->projectModel->declineInvitation((int) $user['id'], (int) $invitationId);
        $statusCode = $result['ok'] ? 200 : 422;
        $this->json($result, $statusCode);
    }

    public function getNotifications(): void
    {
        $user = Auth::user();
        if ($user === null) {
            $this->json(['ok' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $notifications = $this->notificationModel->fetchNotifications((int) $user['id']);
        $unreadCount = $this->notificationModel->getUnreadCount((int) $user['id']);

        $this->json([
            'ok' => true,
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    public function markNotificationRead(string $notificationId): void
    {
        $user = Auth::user();
        if ($user === null) {
            $this->json(['ok' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $updated = $this->notificationModel->markAsRead((int) $notificationId, (int) $user['id']);
        $this->json([
            'ok' => $updated,
            'message' => $updated ? 'Notification marked as read.' : 'Notification not found.',
        ]);
    }

    public function markAllNotificationsRead(): void
    {
        $user = Auth::user();
        if ($user === null) {
            $this->json(['ok' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $this->notificationModel->markAllAsRead((int) $user['id']);
        $this->json(['ok' => true, 'message' => 'All notifications marked as read.']);
    }

    /** @return array<string, mixed> */
    private function readPayload(): array
    {
        $contentType = (string) ($_SERVER['CONTENT_TYPE'] ?? '');
        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input');
            if (!is_string($raw) || $raw === '') {
                return [];
            }

            $decoded = json_decode($raw, true);
            return is_array($decoded) ? $decoded : [];
        }

        return $_POST;
    }

    /** @param array<string, mixed> $payload */
    private function json(array $payload, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($payload, JSON_UNESCAPED_SLASHES);
        exit;
    }
}

