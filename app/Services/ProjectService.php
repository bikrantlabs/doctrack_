<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\ProjectRepository;
use App\Repositories\UserRepository;

final class ProjectService
{
    private const ALLOWED_MEMBER_ROLES = ['reviewer', 'editor', 'viewer'];
    private const ALLOWED_SCOPES = ['all', 'my', 'shared'];

    public function __construct(
        private readonly ProjectRepository $projects,
        private readonly UserRepository $users
    ) {
    }

    /** @return array<int, array{id:int,fullname:string,email:string}> */
    public function searchUsers(string $query, int $excludeUserId): array
    {
        if (mb_strlen(trim($query)) < 2) {
            return [];
        }

        return $this->users->searchByNameOrEmail($query, $excludeUserId, 10);
    }

    /**
     * @param array<int, array{user_id:int,role:string}> $members
     * @return array{ok:bool,message:string,projectId?:int}
     */
    public function createProject(int $ownerId, string $title, string $description, array $members): array
    {
        $normalizedTitle = trim($title);
        if ($normalizedTitle === '') {
            return ['ok' => false, 'message' => 'Project title is required.'];
        }

        if (mb_strlen($normalizedTitle) > 255) {
            return ['ok' => false, 'message' => 'Project title cannot exceed 255 characters.'];
        }

        $normalizedMembers = [];
        foreach ($members as $member) {
            if (!isset($member['user_id'], $member['role'])) {
                return ['ok' => false, 'message' => 'Each member must include user_id and role.'];
            }

            $userId = (int) $member['user_id'];
            $role = trim((string) $member['role']);

            if ($userId <= 0) {
                return ['ok' => false, 'message' => 'Invalid user selected.'];
            }

            if (!in_array($role, self::ALLOWED_MEMBER_ROLES, true)) {
                return ['ok' => false, 'message' => 'Invalid member role selected.'];
            }

            $normalizedMembers[$userId] = ['user_id' => $userId, 'role' => $role];
        }

        $memberIds = array_keys($normalizedMembers);
        if ($memberIds !== []) {
            $existingIds = $this->users->findByIds($memberIds);
            sort($existingIds);
            sort($memberIds);

            if ($existingIds !== $memberIds) {
                return ['ok' => false, 'message' => 'One or more selected users no longer exist.'];
            }
        }

        $projectId = $this->projects->createWithMembers(
            $ownerId,
            $normalizedTitle,
            trim($description),
            array_values($normalizedMembers)
        );

        return [
            'ok' => true,
            'message' => 'Project created successfully.',
            'projectId' => $projectId,
        ];
    }

    /**
     * Fetch user's projects with document and member counts
     * @return array<int, array{id:int, title:string, description:string|null, role:string, created_at:string, documentCount:int, memberCount:int}>
     */
    public function fetchUserProjectsWithStats(int $userId): array
    {
        return $this->projects->getProjectsByScope($userId, 'all');
    }

    /**
     * @return array<int, array{id:int, title:string, description:string|null, role:string, created_at:string, documentCount:int, memberCount:int}>
     */
    public function fetchProjectsByScope(int $userId, string $scope): array
    {
        return $this->projects->getProjectsByScope($userId, $this->normalizeScope($scope));
    }

    /**
     * @return array{id:int, title:string, description:string|null, role:string, created_at:string, documentCount:int, memberCount:int}|null
     */
    public function fetchProjectDetailForUser(int $projectId, int $userId): ?array
    {
        if ($projectId <= 0) {
            return null;
        }

        return $this->projects->getProjectDetailForUser($projectId, $userId);
    }

    public function normalizeScope(string $scope): string
    {
        $scope = trim($scope);
        return in_array($scope, self::ALLOWED_SCOPES, true) ? $scope : 'all';
    }
}
