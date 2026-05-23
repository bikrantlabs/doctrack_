<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\NotificationRepository;
use App\Repositories\ProjectRepository;

final class NotificationService
{
    public function __construct(
        private readonly NotificationRepository $notifications,
        private readonly ProjectRepository $projects
    ) {
    }

    public function createProjectNotification(
        int $projectId,
        int $actorUserId,
        string $type,
        string $title,
        ?string $body,
        ?string $link
    ): void {
        $members = $this->projects->getProjectMembersForUser($projectId, $actorUserId);

        $recipientIds = [];
        foreach ($members as $member) {
            $memberId = (int) $member['id'];
            if ($memberId !== $actorUserId) {
                $recipientIds[] = $memberId;
            }
        }

        if ($recipientIds !== []) {
            $this->notifications->createForMultipleUsers(
                $recipientIds,
                $projectId,
                $type,
                $title,
                $body,
                $link,
                $actorUserId
            );
        }
    }

    public function createUserNotification(
        int $userId,
        ?int $projectId,
        string $type,
        string $title,
        ?string $body,
        ?string $link,
        ?int $createdBy
    ): void {
        $this->notifications->create($userId, $projectId, $type, $title, $body, $link, $createdBy);
    }

    public function fetchNotifications(int $userId): array
    {
        return $this->notifications->getNotificationsForUser($userId);
    }

    public function getUnreadCount(int $userId): int
    {
        return $this->notifications->getUnreadCount($userId);
    }

    public function markAsRead(int $notificationId, int $userId): bool
    {
        return $this->notifications->markAsRead($notificationId, $userId);
    }

    public function markAllAsRead(int $userId): bool
    {
        return $this->notifications->markAllAsRead($userId);
    }
}
