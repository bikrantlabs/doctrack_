<?php
/** @var array{id:int, title:string, description:string|null, role:string, created_at:string, documentCount:int, memberCount:int} $project */
?>

<a href="<?= e(url('/app/projects/' . $project['id'])) ?>" class="project-card-link">
    <div class="project-card">
        <div class="project-card-header">
            <div class="project-icon project-icon-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                </svg>
            </div>
            <button class="project-menu" aria-label="Project menu">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="1"/>
                    <circle cx="19" cy="12" r="1"/>
                    <circle cx="5" cy="12" r="1"/>
                </svg>
            </button>
        </div>
        <div class="project-card-body">
            <h3 class="project-title"><?= e($project['title']) ?></h3>
            <p class="project-description"><?= e($project['description'] ?? '') ?></p>
            <div class="project-stats">
                <div class="project-stat">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                    <span><?= $project['documentCount'] ?> <?= $project['documentCount'] === 1 ? 'doc' : 'docs' ?></span>
                </div>
                <div class="project-stat">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                    </svg>
                    <span><?= $project['memberCount'] ?> <?= $project['memberCount'] === 1 ? 'member' : 'members' ?></span>
                </div>
            </div>
        </div>
        <div class="project-card-footer">
            <span class="project-badge badge-<?= e($project['role']) ?>"><?= ucfirst($project['role']) ?></span>
        </div>
    </div>
</a>

