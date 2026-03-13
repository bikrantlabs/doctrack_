<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Repositories\ProjectRepository;
use App\Repositories\UserRepository;
use App\Services\ProjectService;

final class ProjectController extends Controller
{
    private ProjectService $projectService;

    public function __construct()
    {
        $this->projectService = new ProjectService(new ProjectRepository(), new UserRepository());
    }

    public function searchUsers(): void
    {
        $user = Auth::user();
        if ($user === null) {
            $this->json(['ok' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $query = trim((string) ($_GET['q'] ?? ''));
        $users = $this->projectService->searchUsers($query, (int) $user['id']);

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

        $result = $this->projectService->createProject((int) $user['id'], $title, $description, $members);

        if (!$result['ok']) {
            $this->json($result, 422);
            return;
        }

        $this->flash('success', $result['message']);
        $this->json($result, 201);
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
            http_response_code(404);
            echo 'Page not found';
            return;
        }

        $project = $this->projectService->fetchProjectDetailForUser($id, (int) $user['id']);
        if ($project === null) {
            http_response_code(404);
            echo 'Page not found';
            return;
        }

        $this->render('app/projects/show', [
            'user' => $user,
            'project' => $project,
        ], (string) $project['title']);
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

