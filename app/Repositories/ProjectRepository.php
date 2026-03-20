<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use Throwable;

final class ProjectRepository
{
    /**
     * @param array<int, array{user_id:int,role:string}> $members
     */
    public function createWithMembers(int $ownerId, string $title, string $description, array $members): int
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $projectStatement = $pdo->prepare(
                'INSERT INTO projects (title, description) VALUES (:title, :description)'
            );
            $projectStatement->execute([
                'title' => $title,
                'description' => $description !== '' ? $description : null,
            ]);

            $projectId = (int) $pdo->lastInsertId();

            // Add owner immediately to user_projects
            $ownershipStatement = $pdo->prepare(
                'INSERT INTO user_projects (user_id, project_id, role)
                 VALUES (:user_id, :project_id, :role)
                 ON DUPLICATE KEY UPDATE role = VALUES(role)'
            );

            $ownershipStatement->execute([
                'user_id' => $ownerId,
                'project_id' => $projectId,
                'role' => 'owner',
            ]);

            // Create invitations for invited members (instead of adding them directly to user_projects)
            if (!empty($members)) {
                $invitationStatement = $pdo->prepare(
                    'INSERT INTO project_invitations (project_id, invited_user_id, invited_by, role, status)
                     VALUES (:project_id, :invited_user_id, :invited_by, :role, :status)'
                );

                foreach ($members as $member) {
                    // Skip if member is the owner
                    if ($member['user_id'] === $ownerId) {
                        continue;
                    }

                    $invitationStatement->execute([
                        'project_id' => $projectId,
                        'invited_user_id' => $member['user_id'],
                        'invited_by' => $ownerId,
                        'role' => $member['role'],
                        'status' => 'pending',
                    ]);
                }
            }

            $pdo->commit();

            return $projectId;
        } catch (Throwable $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }

    /**
     * Get all projects for a user (projects they own or participate in)
     * @return array<int, array{id:int, title:string, description:string|null, role:string, created_at:string}>
     */
    public function getAllProjectsByUserId(int $userId): array
    {
        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'SELECT p.id, p.title, p.description, up.role, p.created_at
             FROM projects p
             INNER JOIN user_projects up ON p.id = up.project_id
             WHERE up.user_id = :user_id
             ORDER BY p.created_at DESC'
        );
        $statement->execute(['user_id' => $userId]);
        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get document count for a project
     */
    public function getProjectDocumentCount(int $projectId): int
    {
        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'SELECT COUNT(*) as count FROM documents WHERE project_id = :project_id'
        );
        $statement->execute(['project_id' => $projectId]);
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        return $result ? (int) $result['count'] : 0;
    }

    /**
     * Get member count for a project (accepted members only)
     */
    public function getProjectMemberCount(int $projectId): int
    {
        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'SELECT COUNT(*) as count FROM user_projects WHERE project_id = :project_id'
        );
        $statement->execute(['project_id' => $projectId]);
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        return $result ? (int) $result['count'] : 0;
    }

    /**
     * @return array<int, array{id:int, title:string, description:string|null, role:string, created_at:string, documentCount:int, memberCount:int}>
     */
    public function getProjectsByScope(int $userId, string $scope): array
    {
        $roleCondition = '';
        if ($scope === 'my') {
            $roleCondition = " AND up.role IN ('owner', 'editor')";
        } elseif ($scope === 'shared') {
            // Shared means any project you are part of but do not own.
            $roleCondition = " AND up.role IN ('editor', 'reviewer', 'viewer')";
        }

        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'SELECT p.id,
                    p.title,
                    p.description,
                    up.role,
                    p.created_at,
                    COALESCE(documents_per_project.document_count, 0) AS documentCount,
                    COALESCE(members_per_project.member_count, 0) AS memberCount
             FROM user_projects up
             INNER JOIN projects p ON p.id = up.project_id
             LEFT JOIN (
                 SELECT d.project_id, COUNT(*) AS document_count
                 FROM documents d
                 GROUP BY d.project_id
             ) AS documents_per_project ON documents_per_project.project_id = p.id
             LEFT JOIN (
                 SELECT up2.project_id, COUNT(*) AS member_count
                 FROM user_projects up2
                 GROUP BY up2.project_id
             ) AS members_per_project ON members_per_project.project_id = p.id
             WHERE up.user_id = :user_id' . $roleCondition . '
             ORDER BY p.created_at DESC'
        );

        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @return array{id:int, title:string, description:string|null, role:string, created_at:string, documentCount:int, memberCount:int}|null
     */
    public function getProjectDetailForUser(int $projectId, int $userId): ?array
    {
        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'SELECT p.id,
                    p.title,
                    p.description,
                    up.role,
                    p.created_at,
                    COALESCE(documents_per_project.document_count, 0) AS documentCount,
                    COALESCE(members_per_project.member_count, 0) AS memberCount
             FROM user_projects up
             INNER JOIN projects p ON p.id = up.project_id
             LEFT JOIN (
                 SELECT d.project_id, COUNT(*) AS document_count
                 FROM documents d
                 GROUP BY d.project_id
             ) AS documents_per_project ON documents_per_project.project_id = p.id
             LEFT JOIN (
                 SELECT up2.project_id, COUNT(*) AS member_count
                 FROM user_projects up2
                 GROUP BY up2.project_id
             ) AS members_per_project ON members_per_project.project_id = p.id
             WHERE up.user_id = :user_id AND p.id = :project_id
             LIMIT 1'
        );

        $statement->execute([
            'user_id' => $userId,
            'project_id' => $projectId,
        ]);

        $project = $statement->fetch(\PDO::FETCH_ASSOC);

        return $project ?: null;
    }

    /**
     * @return array<int, array{id:int, fullname:string, email:string, role:string, joined_at:string}>
     */
    public function getProjectMembersForUser(int $projectId, int $requestingUserId): array
    {
        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'SELECT up.user_id AS id,
                    u.fullname,
                    u.email,
                    up.role,
                    up.created_at AS joined_at
             FROM user_projects up
             INNER JOIN users u ON u.id = up.user_id
             WHERE up.project_id = :project_id
               AND EXISTS (
                    SELECT 1
                    FROM user_projects access_up
                    WHERE access_up.project_id = up.project_id
                      AND access_up.user_id = :requesting_user_id
               )
             ORDER BY FIELD(up.role, \'owner\', \'editor\', \'reviewer\', \'viewer\'), u.fullname ASC'
        );

        $statement->execute([
            'project_id' => $projectId,
            'requesting_user_id' => $requestingUserId,
        ]);

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getUserRoleInProject(int $projectId, int $userId): ?string
    {
        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'SELECT role
             FROM user_projects
             WHERE project_id = :project_id AND user_id = :user_id
             LIMIT 1'
        );

        $statement->execute([
            'project_id' => $projectId,
            'user_id' => $userId,
        ]);

        $role = $statement->fetchColumn();
        return is_string($role) ? $role : null;
    }

    public function updateMemberRole(int $projectId, int $memberUserId, string $role): bool
    {
        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'UPDATE user_projects
             SET role = :role
             WHERE project_id = :project_id
               AND user_id = :member_user_id
               AND role <> \'owner\''
        );

        $statement->execute([
            'project_id' => $projectId,
            'member_user_id' => $memberUserId,
            'role' => $role,
        ]);

        return $statement->rowCount() > 0;
    }

    public function removeMember(int $projectId, int $memberUserId): bool
    {
        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'DELETE FROM user_projects
             WHERE project_id = :project_id
               AND user_id = :member_user_id
               AND role <> \'owner\''
        );

        $statement->execute([
            'project_id' => $projectId,
            'member_user_id' => $memberUserId,
        ]);

        return $statement->rowCount() > 0;
    }
}
