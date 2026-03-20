<?php
/**
 * @var int $pendingInvitationCount
 * @var array<int, array{id:int, project_id:int, project_title:string, invited_by_id:int, invited_by_name:string, role:string, status:string, created_at:string}> $pendingInvitations
 */
?>

<div class="notifications-dropdown" data-invitations-root="1" data-invitations-data="<?= htmlspecialchars(json_encode($pendingInvitations ?? []), ENT_QUOTES, 'UTF-8') ?>" hidden aria-hidden="true">
    <div class="notifications-list">
        <?php if (!empty($pendingInvitations)): ?>
            <?php foreach ($pendingInvitations as $invitation): ?>
                <div class="notification-item invitation-item" data-invitation-id="<?= (int) $invitation['id'] ?>">
                    <div class="invitation-content">
                        <div class="invitation-header">
                            <h4 class="invitation-project"><?= e($invitation['project_title']) ?></h4>
                            <span class="invitation-role badge-<?= e($invitation['role']) ?>"><?= ucfirst($invitation['role']) ?></span>
                        </div>
                        <p class="invitation-from">Invited by <strong><?= e($invitation['invited_by_name']) ?></strong></p>
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
        <?php else: ?>
            <div class="notifications-empty">
                <p>No pending invitations</p>
            </div>
        <?php endif; ?>
    </div>
</div>

