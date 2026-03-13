<?php
/** @var array{id:int, title:string, description:string|null, role:string, created_at:string, documentCount:int, memberCount:int} $project */
$currentUrl = current_path();
$description = trim((string) ($project['description'] ?? ''));
?>

<div class="dashboard-layout">
    <?php require BASE_PATH . '/views/app/components/app-sidebar.php'; ?>

    <main class="dashboard-main">
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
                    <a href="<?= e(url('/app')) ?>" class="breadcrumb-link">Projects</a>
                    <span class="breadcrumb-separator">/</span>
                    <span><?= e($project['title']) ?></span>
                </div>
            </div>
        </header>

        <div class="dashboard-content">
            <div class="page-header">
                <div class="page-header-left">
                    <h1 class="page-title"><?= e($project['title']) ?></h1>
                    <p class="page-subtitle">
                        <?= $description !== '' ? e($description) : 'No description added yet.' ?>
                    </p>
                </div>
                <div class="page-header-right">
                    <a href="<?= e(url('/app')) ?>" class="btn btn-outline">Back to Projects</a>
                </div>
            </div>

            <div class="projects-grid">
                <div class="project-card">
                    <div class="project-card-body">
                        <h3 class="project-title">Project Overview</h3>
                        <div class="project-stats" style="margin-top: 12px;">
                            <div class="project-stat">
                                <span class="project-badge badge-<?= e($project['role']) ?>"><?= e(ucfirst($project['role'])) ?></span>
                            </div>
                            <div class="project-stat">
                                <span><?= (int) $project['documentCount'] ?> <?= (int) $project['documentCount'] === 1 ? 'document' : 'documents' ?></span>
                            </div>
                            <div class="project-stat">
                                <span><?= (int) $project['memberCount'] ?> <?= (int) $project['memberCount'] === 1 ? 'member' : 'members' ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

