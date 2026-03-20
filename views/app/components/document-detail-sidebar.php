<?php
/** @var array{id:int,project_id:int,title:string,current_version_id:int,created_at:string,project_role:string} $document */
/** @var array{id:int,document_id:int,version_number:int,file_path:string,file_type:string,uploaded_by:int,uploaded_by_name:string|null,change_summary:string|null,status:string|null,is_locked:int,created_at:string} $selectedVersion */
/** @var array<int, array{id:int,document_id:int,version_number:int,file_path:string,file_type:string,uploaded_by:int,uploaded_by_name:string|null,change_summary:string|null,status:string|null,is_locked:int,created_at:string}> $versions */
?>

<aside class="document-detail-sidebar">
    <section class="document-detail-card">
        <a class="document-back-link" href="<?= e(url('/app/projects/' . (int) $document['project_id'])) ?>">Back to Project</a>
        <h2>Version History</h2>
        <div class="document-versions-list">
            <?php foreach ($versions as $version): ?>
                <?php
                $isActive = (int) $version['id'] === (int) $selectedVersion['id'];
                $href = url('/app/projects/' . (int) $document['project_id'] . '/' . (int) $document['id'] . '?version=' . (int) $version['version_number']);
                ?>
                <a href="<?= e($href) ?>" class="document-version-item <?= $isActive ? 'active' : '' ?>">
                    <div>
                        <strong>Version <?= (int) $version['version_number'] ?></strong>
                        <span><?= e(strtoupper((string) $version['file_type'])) ?></span>
                    </div>
                    <span class="project-document-chip chip-status status-<?= e((string) ($version['status'] ?? 'draft')) ?>">
                        <?= e((string) ($version['status'] ?? 'draft')) ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <?php require BASE_PATH . '/views/app/components/document-review-threads-card.php'; ?>
</aside>

