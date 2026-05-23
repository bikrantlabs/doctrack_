<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class NotificationRepository
{
    public function create(
        int $userId,
        ?int $projectId,
        string $type,
        string $title,
        ?string $body,
        ?string $link,
        ?int $createdBy
    ): int {
        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'INSERT INTO notifications (user_id, project_id, type, title, body, link, created_by)
             VALUES (:user_id, :project_id, :type, :title, :body, :link, :created_by)'
        );

        $statement->execute([
            'user_id' => $userId,
            'project_id' => $projectId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'link' => $link,
            'created_by' => $createdBy,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public function createForMultipleUsers(
        array $userIds,
        ?int $projectId,
        string $type,
        string $title,
        ?string $body,
        ?string $link,
        ?int $createdBy
    ): int {
        if ($userIds === []) {
            return 0;
        }

        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'INSERT INTO notifications (user_id, project_id, type, title, body, link, created_by)
             VALUES (:user_id, :project_id, :type, :title, :body, :link, :created_by)'
        );

        $created = 0;
        foreach ($userIds as $userId) {
            $statement->execute([
                'user_id' => $userId,
                'project_id' => $projectId,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'link' => $link,
                'created_by' => $createdBy,
            ]);
            $created++;
        }

        return $created;
    }

    public function getNotificationsForUser(int $userId, int $limit = 20): array
    {
        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'SELECT n.id,
                    n.user_id,
                    n.project_id,
                    n.type,
                    n.title,
                    n.body,
                    n.link,
                    n.created_by,
                    n.is_read,
                    n.created_at,
                    creator.fullname AS created_by_name
             FROM notifications n
             LEFT JOIN users creator ON creator.id = n.created_by
             WHERE n.user_id = :user_id
             ORDER BY n.created_at DESC
             LIMIT :limit'
        );

        $statement->bindValue('user_id', $userId, \PDO::PARAM_INT);
        $statement->bindValue('limit', $limit, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getUnreadCount(int $userId): int
    {
        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'SELECT COUNT(*) FROM notifications
             WHERE user_id = :user_id AND is_read = 0'
        );

        $statement->execute(['user_id' => $userId]);

        return (int) $statement->fetchColumn();
    }

    public function markAsRead(int $notificationId, int $userId): bool
    {
        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'UPDATE notifications
             SET is_read = 1
             WHERE id = :id AND user_id = :user_id'
        );

        $statement->execute([
            'id' => $notificationId,
            'user_id' => $userId,
        ]);

        return $statement->rowCount() > 0;
    }

    public function markAllAsRead(int $userId): bool
    {
        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'UPDATE notifications
             SET is_read = 1
             WHERE user_id = :user_id AND is_read = 0'
        );

        $statement->execute(['user_id' => $userId]);

        return $statement->rowCount() > 0;
    }
}
