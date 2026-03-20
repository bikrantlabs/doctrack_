<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use Throwable;

final class InvitationRepository
{
    /**
     * @return array<int, array{id:int, project_id:int, project_title:string, invited_by_id:int, invited_by_name:string, role:string, status:string, created_at:string}>
     */
    public function getPendingInvitationsForUser(int $userId): array
    {
        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'SELECT pi.id,
                    pi.project_id,
                    p.title AS project_title,
                    pi.invited_by AS invited_by_id,
                    u.fullname AS invited_by_name,
                    pi.role,
                    pi.status,
                    pi.created_at
             FROM project_invitations pi
             INNER JOIN projects p ON p.id = pi.project_id
             INNER JOIN users u ON u.id = pi.invited_by
             WHERE pi.invited_user_id = :user_id
               AND pi.status = \'pending\'
             ORDER BY pi.created_at DESC'
        );

        $statement->execute(['user_id' => $userId]);
        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @return array{id:int, project_id:int, invited_user_id:int, invited_by:int, role:string, status:string}|null
     */
    public function getInvitationById(int $invitationId): ?array
    {
        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'SELECT id, project_id, invited_user_id, invited_by, role, status
             FROM project_invitations
             WHERE id = :id
             LIMIT 1'
        );

        $statement->execute(['id' => $invitationId]);
        $invitation = $statement->fetch(\PDO::FETCH_ASSOC);
        return $invitation ?: null;
    }

    public function updateInvitationStatus(int $invitationId, string $status): bool
    {
        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'UPDATE project_invitations
             SET status = :status
             WHERE id = :id
               AND status = \'pending\''
        );

        $statement->execute([
            'id' => $invitationId,
            'status' => $status,
        ]);

        return $statement->rowCount() > 0;
    }

    /**
     * Add user to project when accepting invitation
     */
    public function addUserToProject(int $userId, int $projectId, string $role): bool
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $statement = $pdo->prepare(
                'INSERT INTO user_projects (user_id, project_id, role)
                 VALUES (:user_id, :project_id, :role)
                 ON DUPLICATE KEY UPDATE role = VALUES(role)'
            );

            $statement->execute([
                'user_id' => $userId,
                'project_id' => $projectId,
                'role' => $role,
            ]);

            $pdo->commit();
            return true;
        } catch (Throwable $exception) {
            $pdo->rollBack();
            return false;
        }
    }

    /**
     * Get count of pending invitations for user
     */
    public function getPendingInvitationCount(int $userId): int
    {
        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'SELECT COUNT(*) as count
             FROM project_invitations
             WHERE invited_user_id = :user_id
               AND status = \'pending\''
        );

        $statement->execute(['user_id' => $userId]);
        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        return $result ? (int) $result['count'] : 0;
    }
}

