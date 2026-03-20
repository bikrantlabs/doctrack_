<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\ProjectRepository;
use App\Repositories\UserRepository;

final class ProjectService
{
    private const ALLOWED_MEMBER_ROLES = ['reviewer', 'editor', 'viewer'];
    private const MANAGEABLE_MEMBER_ROLES = ['reviewer', 'editor', 'viewer'];
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

    /**
     * @return array<int, array{id:int, fullname:string, email:string, role:string, joined_at:string}>
     */
    public function fetchProjectMembersForUser(int $projectId, int $requestingUserId): array
    {
        if ($projectId <= 0 || $requestingUserId <= 0) {
            return [];
        }

        return $this->projects->getProjectMembersForUser($projectId, $requestingUserId);
    }

    /** @return array{ok:bool,message:string} */
    public function changeMemberRole(int $projectId, int $actingUserId, int $memberUserId, string $role): array
    {
        if ($projectId <= 0 || $actingUserId <= 0 || $memberUserId <= 0) {
            return ['ok' => false, 'message' => 'Invalid request.'];
        }

        $normalizedRole = trim($role);
        if (!in_array($normalizedRole, self::MANAGEABLE_MEMBER_ROLES, true)) {
            return ['ok' => false, 'message' => 'Invalid member role selected.'];
        }

        $actingRole = $this->projects->getUserRoleInProject($projectId, $actingUserId);
        if ($actingRole !== 'owner') {
            return ['ok' => false, 'message' => 'Only project owners can change member roles.'];
        }

        if ($actingUserId === $memberUserId) {
            return ['ok' => false, 'message' => 'Owners cannot change their own role.'];
        }

        $memberCurrentRole = $this->projects->getUserRoleInProject($projectId, $memberUserId);
        if ($memberCurrentRole === null) {
            return ['ok' => false, 'message' => 'Member not found in this project.'];
        }

        if ($memberCurrentRole === 'owner') {
            return ['ok' => false, 'message' => 'Owner role cannot be changed.'];
        }

        if ($memberCurrentRole === $normalizedRole) {
            return ['ok' => true, 'message' => 'Member role is already up to date.'];
        }

        $updated = $this->projects->updateMemberRole($projectId, $memberUserId, $normalizedRole);
        if (!$updated) {
            return ['ok' => false, 'message' => 'Could not update member role.'];
        }

        return ['ok' => true, 'message' => 'Member role updated successfully.'];
    }

    /** @return array{ok:bool,message:string} */
    public function removeMemberFromProject(int $projectId, int $actingUserId, int $memberUserId): array
    {
        if ($projectId <= 0 || $actingUserId <= 0 || $memberUserId <= 0) {
            return ['ok' => false, 'message' => 'Invalid request.'];
        }

        $actingRole = $this->projects->getUserRoleInProject($projectId, $actingUserId);
        if ($actingRole !== 'owner') {
            return ['ok' => false, 'message' => 'Only project owners can remove members.'];
        }

        if ($actingUserId === $memberUserId) {
            return ['ok' => false, 'message' => 'Owners cannot remove themselves.'];
        }

        $memberCurrentRole = $this->projects->getUserRoleInProject($projectId, $memberUserId);
        if ($memberCurrentRole === null) {
            return ['ok' => false, 'message' => 'Member not found in this project.'];
        }

        if ($memberCurrentRole === 'owner') {
            return ['ok' => false, 'message' => 'Owner cannot be removed from the project.'];
        }

        $removed = $this->projects->removeMember($projectId, $memberUserId);
        if (!$removed) {
            return ['ok' => false, 'message' => 'Could not remove member.'];
        }

        return ['ok' => true, 'message' => 'Member removed from project.'];
    }

    /**
     * @return array<int, array{id:int, project_id:int, project_title:string, invited_by_id:int, invited_by_name:string, role:string, status:string, created_at:string}>
     */
    public function fetchPendingInvitations(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }

        return (new \App\Repositories\InvitationRepository())->getPendingInvitationsForUser($userId);
    }

    /**
     * Get count of pending invitations for user
     */
    public function getPendingInvitationCount(int $userId): int
    {
        if ($userId <= 0) {
            return 0;
        }

        return (new \App\Repositories\InvitationRepository())->getPendingInvitationCount($userId);
    }

    /** @return array{ok:bool,message:string} */
    public function acceptInvitation(int $userId, int $invitationId): array
    {
        if ($userId <= 0 || $invitationId <= 0) {
            return ['ok' => false, 'message' => 'Invalid request.'];
        }

        $invitationRepo = new \App\Repositories\InvitationRepository();
        $invitation = $invitationRepo->getInvitationById($invitationId);

        if ($invitation === null) {
            return ['ok' => false, 'message' => 'Invitation not found.'];
        }

        if ((int) $invitation['invited_user_id'] !== $userId) {
            return ['ok' => false, 'message' => 'This invitation is not for you.'];
        }

        if ($invitation['status'] !== 'pending') {
            return ['ok' => false, 'message' => 'This invitation has already been ' . $invitation['status'] . '.'];
        }

        $projectId = (int) $invitation['project_id'];
        $role = (string) $invitation['role'];

        $statusUpdated = $invitationRepo->updateInvitationStatus($invitationId, 'accepted');
        if (!$statusUpdated) {
            return ['ok' => false, 'message' => 'Could not update invitation.'];
        }

        $userAdded = $invitationRepo->addUserToProject($userId, $projectId, $role);
        if (!$userAdded) {
            return ['ok' => false, 'message' => 'Could not add you to the project.'];
        }

        return ['ok' => true, 'message' => 'Invitation accepted! You now have access to the project.'];
    }

    /** @return array{ok:bool,message:string} */
    public function declineInvitation(int $userId, int $invitationId): array
    {
        if ($userId <= 0 || $invitationId <= 0) {
            return ['ok' => false, 'message' => 'Invalid request.'];
        }

        $invitationRepo = new \App\Repositories\InvitationRepository();
        $invitation = $invitationRepo->getInvitationById($invitationId);

        if ($invitation === null) {
            return ['ok' => false, 'message' => 'Invitation not found.'];
        }

        if ((int) $invitation['invited_user_id'] !== $userId) {
            return ['ok' => false, 'message' => 'This invitation is not for you.'];
        }

        if ($invitation['status'] !== 'pending') {
            return ['ok' => false, 'message' => 'This invitation has already been ' . $invitation['status'] . '.'];
        }

        $statusUpdated = $invitationRepo->updateInvitationStatus($invitationId, 'rejected');
        if (!$statusUpdated) {
            return ['ok' => false, 'message' => 'Could not update invitation.'];
        }

        return ['ok' => true, 'message' => 'Invitation declined.'];
    }
}
