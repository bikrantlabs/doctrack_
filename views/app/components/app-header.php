<?php

use App\Utils\SVGRenderer;

/** @var array{id:int,email:string,fullname:string} $user */

$userName = trim((string)($user['fullname'] ?? 'User'));
$userEmail = trim((string)($user['email'] ?? ''));
$nameParts = preg_split('/\s+/', $userName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
$initials = '?';
if (count($nameParts) >= 2) {
    $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1));
} elseif (count($nameParts) === 1) {
    $initials = strtoupper(substr($nameParts[0], 0, 2));
}

$breadcrumbs = isset($breadcrumbs) && is_array($breadcrumbs) ? $breadcrumbs : [];
$showSearch = isset($showSearch) ? (bool)$showSearch : false;
$showNotifications = isset($showNotifications) ? (bool)$showNotifications : false;
$pendingInvitationCount = isset($pendingInvitationCount) ? (int)$pendingInvitationCount : 0;
$pendingInvitations = isset($pendingInvitations) ? (array)$pendingInvitations : [];
?>

<header class="dashboard-header">
    <div class="dashboard-header-left">
        <a href="<?= e(url('/app')) ?>" class="app-navbar-logo">
            <?php
            SVGRenderer::renderInline('logo', [
                    'width' => '24',
                    'height' => '24',
                    'class' => 'app-navbar-logo-icon',
            ]);
            ?>
            <span>DocuFlow</span>
        </a>

        <?php if ($breadcrumbs !== []): ?>
            <div class="dashboard-breadcrumb">
                <?php foreach ($breadcrumbs as $index => $breadcrumb): ?>
                    <?php if ($index > 0): ?>
                        <span class="breadcrumb-separator">/</span>
                    <?php endif; ?>

                    <?php if (isset($breadcrumb['href']) && (string)$breadcrumb['href'] !== ''): ?>
                        <a href="<?= e(url((string)$breadcrumb['href'])) ?>" class="breadcrumb-link">
                            <?= e((string)($breadcrumb['label'] ?? '')) ?>
                        </a>
                    <?php else: ?>
                        <span><?= e((string)($breadcrumb['label'] ?? '')) ?></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="dashboard-header-right">
        <?php if ($showSearch): ?>
            <div class="dashboard-search">
                <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="M21 21l-4.35-4.35"/>
                </svg>
                <input type="text" placeholder="Search projects..." class="search-input" aria-label="Search projects">
            </div>
        <?php endif; ?>

        <?php if ($showNotifications): ?>
            <div class="notifications-wrapper">
                <button class="header-icon-btn notifications-trigger" aria-label="Notifications"
                        data-notifications-trigger="1">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                    <?php if ($pendingInvitationCount > 0): ?>
                        <span class="notification-badge"><?= min($pendingInvitationCount, 9) ?><?= $pendingInvitationCount > 9 ? '+' : '' ?></span>
                    <?php endif; ?>
                </button>
                <?php require BASE_PATH . '/views/app/components/notifications-dropdown.php'; ?>
            </div>
        <?php endif; ?>

        <div class="app-navbar-user" aria-label="Current user">
            <div class="sidebar-user-avatar">
                <span><?= e($initials) ?></span>
            </div>
            <div class="sidebar-user-info">
                <span class="sidebar-user-name"><?= e($userName) ?></span>
                <span class="sidebar-user-email"><?= e($userEmail) ?></span>
            </div>
        </div>
        <a href="<?= e(url('/logout')) ?>" class="btn btn-primary btn-sm">Logout</a>
    </div>
</header>
