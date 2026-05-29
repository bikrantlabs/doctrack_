<?php
$currentUrl = current_path();
$activeScope = isset($activeScope) ? (string) $activeScope : 'all';
$pendingInvitationCount = isset($pendingInvitationCount) ? (int) $pendingInvitationCount : 0;
$pendingInvitations = isset($pendingInvitations) ? (array) $pendingInvitations : [];
$notifications = isset($notifications) ? (array) $notifications : [];
$notificationUnreadCount = isset($notificationUnreadCount) ? (int) $notificationUnreadCount : 0;
$scopeTabs = [
    'all' => 'All Projects',
    'my' => 'My Projects',
    'shared' => 'Shared with me',
];

$scopeSubtitle = [
    'all' => 'Manage and organize your document projects',
    'my' => 'Projects you own or can edit',
    'shared' => 'Projects shared with you where you are not the owner',
];
?>
<!-- Projects Page - Main Container -->

<div class="dashboard-layout">
    <!-- Main Content -->
    <main class="dashboard-main">

        <?php
        $breadcrumbs = [
            ['label' => 'Dashboard', 'href' => '/app'],
            ['label' => 'Projects'],
        ];
        $showSearch = true;
        $showNotifications = true;
        require BASE_PATH . '/views/app/components/app-header.php';
        ?>

        <!-- Projects Content -->
        <div class="dashboard-content">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-header-left">
                    <h1 class="page-title">Projects</h1>
                    <p class="page-subtitle"><?= e($scopeSubtitle[$activeScope] ?? $scopeSubtitle['all']) ?></p>
                </div>
                <div class="page-header-right">
                   
                    <button class="btn btn-primary" data-modal="create-project-modal">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="5" x2="12" y2="19"/>
                            <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        New Project
                    </button>
                </div>
            </div>

            <!-- Filters Bar -->
            <div class="filters-bar">
                <div class="filters-left">
                    <div class="filter-tabs">
                        <?php foreach ($scopeTabs as $scopeKey => $scopeLabel): ?>
                            <?php $isActiveScope = $activeScope === $scopeKey; ?>
                            <a
                                href="<?= e(url('/app?scope=' . $scopeKey)) ?>"
                                class="filter-tab <?= $isActiveScope ? 'active' : '' ?>"
                            >
                                <?= e($scopeLabel) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                
            </div>

            <!-- Projects Grid -->
            <div class="projects-grid">
                <!-- Dynamically render project cards -->
                <?php if (!empty($projects)): ?>
                    <?php foreach ($projects as $project): ?>
                        <?php require BASE_PATH . '/views/app/components/project-card.php'; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                        </svg>
                        <?php if ($activeScope === 'my'): ?>
                            <h3>No projects in My Projects</h3>
                            <p>Create a project or ask for editor access to appear here.</p>
                        <?php elseif ($activeScope === 'shared'): ?>
                            <h3>No shared projects yet</h3>
                            <p>Shared projects where you are reviewer/viewer will appear here.</p>
                        <?php else: ?>
                            <h3>No projects yet</h3>
                            <p>Create your first project to get started</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php require BASE_PATH . '/views/app/components/create-new-project-card.php'; ?>
            </div>
        </div>
    </main>
</div>

<?php require BASE_PATH . '/views/app/modals/create-new-project.php'; ?>

