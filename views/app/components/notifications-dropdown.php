<?php
/**
 * @var int $pendingInvitationCount
 * @var array<int, array{id:int, project_id:int, project_title:string, invited_by_id:int, invited_by_name:string, role:string, status:string, created_at:string}> $pendingInvitations
 * @var array<int, array{id:int, user_id:int, project_id:int|null, type:string, title:string, body:string|null, link:string|null, created_by:int|null, created_by_name:string|null, is_read:int, created_at:string}> $notifications
 * @var int $notificationUnreadCount
 */
$hasInvitations = !empty($pendingInvitations);
$hasNotifications = !empty($notifications);
$hasAny = $hasInvitations || $hasNotifications;
$hasUnread = $notificationUnreadCount > 0;
?>
<div class="notifications-dropdown" data-notifications-root="1"
     data-invitations-data="<?= htmlspecialchars(json_encode($pendingInvitations ?? []), ENT_QUOTES, 'UTF-8') ?>"
     data-notifications-data="<?= htmlspecialchars(json_encode($notifications ?? []), ENT_QUOTES, 'UTF-8') ?>"
     hidden aria-hidden="true">
    <div class="notifications-list">
        <?php if ($hasAny): ?>

            <?php if ($hasInvitations): ?>
                <div class="notifications-section-label">Invitations</div>
                <?php foreach ($pendingInvitations as $invitation): ?>
                    <div class="notification-item invitation-item" data-invitation-id="<?= (int) $invitation['id'] ?>">
                        <div class="notification-content">
                            <div class="notification-header">
                                <span class="notification-title">Invitation to join
                                    <strong><?= e($invitation['project_title']) ?></strong>
                                </span>
                                <span class="invitation-role badge-<?= e($invitation['role']) ?>"><?= ucfirst($invitation['role']) ?></span>
                            </div>
                            <p class="notification-meta">Invited by <strong><?= e($invitation['invited_by_name']) ?></strong></p>
                        </div>
                        <div class="invitation-actions">
                            <button type="button" class="btn btn-sm btn-primary invitation-accept-btn" data-invitation-id="<?= (int) $invitation['id'] ?>">
                                Accept
                            </button>
                            <button type="button" class="btn btn-sm btn-outline invitation-decline-btn" data-invitation-id="<?= (int) $invitation['id'] ?>">
                                Decline
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if ($hasNotifications): ?>
                <?php if ($hasInvitations): ?>
                    <div class="notifications-divider"></div>
                <?php endif; ?>
                <div class="notifications-section-label">Activity</div>
                <?php foreach ($notifications as $notification): ?>
                    <?php
                    $notifId = (int) $notification['id'];
                    $notifLink = $notification['link'] !== null ? e($notification['link']) : '#';
                    $isRead = (int) $notification['is_read'];
                    $createdByName = $notification['created_by_name'] !== null ? e($notification['created_by_name']) : null;
                    ?>
                    <a class="notification-item notification-type-<?= e($notification['type']) ?> <?= $isRead ? '' : 'notification-unread' ?>"
                       href="<?= url($notifLink) ?>"
                       data-notification-id="<?= $notifId ?>"
                       data-is-read="<?= $isRead ?>">
                        <div class="notification-content">
                            <div class="notification-title"><?= e($notification['title']) ?></div>
                            <?php if ($notification['body'] !== null && $notification['body'] !== ''): ?>
                                <p class="notification-body"><?= e($notification['body']) ?></p>
                            <?php endif; ?>
                            <div class="notification-meta">
                                <?php if ($createdByName !== null): ?>
                                    by <?= $createdByName ?> &middot;
                                <?php endif; ?>
                                <?= e($notification['created_at']) ?>
                            </div>
                        </div>
                        <?php if (!$isRead): ?>
                            <span class="notification-unread-dot" aria-label="Unread"></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>

        <?php else: ?>
            <div class="notifications-empty">
                <p>No notifications yet</p>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($hasUnread): ?>
        <div class="notifications-footer">
            <button type="button" class="mark-all-read-btn" data-mark-all-read="1">Mark all as read</button>
        </div>
    <?php endif; ?>
</div>
