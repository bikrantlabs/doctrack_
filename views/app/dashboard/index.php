<?php
$currentUrl = current_path();
$activeScope = isset($activeScope) ? (string) $activeScope : 'all';
$scopeTabs = [
    'all' => 'All Projects',
    'my' => 'My Projects',
    'shared' => 'Shared with me',
];

$scopeSubtitle = [
    'all' => 'Manage and organize your document projects',
    'my' => 'Projects you own or can edit',
    'shared' => 'Projects shared with you as reviewer or viewer',
];
?>
<!-- Projects Page - Main Container -->

<div class="dashboard-layout">
    <!-- Sidebar (same as dashboard) -->
    <?php require BASE_PATH . '/views/app/components/app-sidebar.php'; ?>


    <!-- Main Content -->
    <main class="dashboard-main">

        <!-- Header -->
        <header class="dashboard-header">
            <div class="dashboard-header-left">
                <button class="sidebar-toggle" aria-label="Toggle sidebar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="3" y1="12" x2="21" y2="12"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>
                <div class="dashboard-breadcrumb">
                    <a href="<?= e(url('/app')) ?>" class="breadcrumb-link">Dashboard</a>
                    <span class="breadcrumb-separator">/</span>
                    <span>Projects</span>
                </div>
            </div>
            <div class="dashboard-header-right">
                <div class="dashboard-search">
                    <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="M21 21l-4.35-4.35"/>
                    </svg>
                    <input type="text" placeholder="Search projects..." class="search-input" aria-label="Search projects">
                </div>
                <button class="header-icon-btn" aria-label="Notifications">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                    <span class="notification-dot"></span>
                </button>
            </div>
        </header>

        <!-- Projects Content -->
        <div class="dashboard-content">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-header-left">
                    <h1 class="page-title">Projects</h1>
                    <p class="page-subtitle"><?= e($scopeSubtitle[$activeScope] ?? $scopeSubtitle['all']) ?></p>
                </div>
                <div class="page-header-right">
                    <button class="btn btn-outline">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="17 8 12 3 7 8"/>
                            <line x1="12" y1="3" x2="12" y2="15"/>
                        </svg>
                        Import
                    </button>
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
                <div class="filters-right">
                    <div class="view-toggle">
                        <button class="view-btn active" aria-label="Grid view">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="7" height="7"/>
                                <rect x="14" y="3" width="7" height="7"/>
                                <rect x="14" y="14" width="7" height="7"/>
                                <rect x="3" y="14" width="7" height="7"/>
                            </svg>
                        </button>
                        <button class="view-btn" aria-label="List view">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="8" y1="6" x2="21" y2="6"/>
                                <line x1="8" y1="12" x2="21" y2="12"/>
                                <line x1="8" y1="18" x2="21" y2="18"/>
                                <line x1="3" y1="6" x2="3.01" y2="6"/>
                                <line x1="3" y1="12" x2="3.01" y2="12"/>
                                <line x1="3" y1="18" x2="3.01" y2="18"/>
                            </svg>
                        </button>
                    </div>
                    <select class="filter-select" aria-label="Sort projects">
                        <option>Sort by: Recent</option>
                        <option>Sort by: Name</option>
                        <option>Sort by: Documents</option>
                        <option>Sort by: Members</option>
                    </select>
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
<script src="<?= e(url('/js/app/projects.js')) ?>" defer></script>

