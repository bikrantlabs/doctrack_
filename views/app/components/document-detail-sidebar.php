<?php
/** @var array{id:int,project_id:int,title:string,current_version_id:int,created_at:string,project_role:string} $document */
/** @var array{id:int,document_id:int,version_number:int,file_path:string,file_type:string,uploaded_by:int,uploaded_by_name:string|null,change_summary:string|null,status:string|null,is_locked:int,created_at:string} $selectedVersion */
/** @var array<int, array{id:int,document_id:int,version_number:int,file_path:string,file_type:string,uploaded_by:int,uploaded_by_name:string|null,change_summary:string|null,status:string|null,is_locked:int,created_at:string}> $versions */
/** @var array<int, array{id:int,title:string,created_by:int,created_by_name:string|null,created_at:string,status:string,selected_version_status:string,open_version_numbers:array<int,int>,comments:array<int, array{id:int,review_thread_id:int,document_version_id:int,version_number:int,reviewer_id:int,reviewer_name:string|null,page_number:int,comment:string,created_at:string}>}> $threads */
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
                <a href="<?= e($href) ?>"
                   class="document-version-item <?= $isActive ? 'active' : '' ?>"
                   <?= $isActive ? 'aria-current="page"' : '' ?>>
                    <div>
                        <strong>Version <?= (int) $version['version_number'] ?></strong>
                        <span>
                            <?= e(strtoupper((string) $version['file_type'])) ?>
                            <?php if ($isActive): ?>
                                <span class="version-selected-label">Selected</span>
                            <?php endif; ?>
                        </span>
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

