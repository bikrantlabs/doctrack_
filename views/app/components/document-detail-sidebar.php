<?php
/** @var array{id:int,project_id:int,title:string,current_version_id:int,created_at:string,project_role:string,is_approved?:bool,approved_version_number?:int|null} $document */
/** @var array{id:int,document_id:int,version_number:int,file_path:string,file_type:string,uploaded_by:int,uploaded_by_name:string|null,change_summary:string|null,status:string|null,is_locked:int,created_at:string} $selectedVersion */
/** @var array<int, array{id:int,document_id:int,version_number:int,file_path:string,file_type:string,uploaded_by:int,uploaded_by_name:string|null,change_summary:string|null,status:string|null,is_locked:int,created_at:string}> $versions */
/** @var array<int, array{id:int,title:string,created_by:int,created_by_name:string|null,created_at:string,status:string,selected_version_status:string,open_version_numbers:array<int,int>,comments:array<int, array{id:int,review_thread_id:int,document_version_id:int,version_number:int,reviewer_id:int,reviewer_name:string|null,page_number:int,comment:string,created_at:string}>}> $threads */
$isApprovedDocument = (bool) ($document['is_approved'] ?? false);
$selectedVersionStatus = (string) ($selectedVersion['status'] ?? 'draft');
$isLatestVersion = (int) $selectedVersion['id'] === (int) $document['current_version_id'];
$hasUnresolvedThreadForSelectedVersion = false;
foreach ($threads as $thread) {
    if ((string) ($thread['selected_version_status'] ?? 'open') !== 'resolved') {
        $hasUnresolvedThreadForSelectedVersion = true;
        break;
    }
}

$canUploadNewVersion = !$isApprovedDocument && in_array((string) ($document['project_role'] ?? ''), ['owner', 'editor'], true);
$canApproveVersion = !$isApprovedDocument
    && $isLatestVersion
    && $selectedVersionStatus !== 'approved'
    && (string) ($document['project_role'] ?? '') === 'reviewer';
$approveDisabledReason = '';
if ($canApproveVersion && $hasUnresolvedThreadForSelectedVersion) {
    $approveDisabledReason = 'Resolve all review threads before approving this version.';
}
$approveVersionUrl = url('/app/projects/' . (int) $document['project_id'] . '/' . (int) $document['id'] . '/approve');
?>

<aside class="document-detail-sidebar">
    <section class="document-detail-card">
        <a class="document-back-link" href="<?= e(url('/app/projects/' . (int) $document['project_id'])) ?>">Back to Project</a>
        <div class="document-card-heading-row">
            <h2>Version History</h2>
            <?php if ($canUploadNewVersion): ?>
                <button type="button" class="btn btn-primary document-version-upload-btn" data-modal="upload-document-version-modal">
                    Upload New Version
                </button>
            <?php elseif ($canApproveVersion): ?>
                <button type="button"
                        class="btn btn-outline document-approve-btn"
                        data-modal="approve-version-modal"
                        <?= $approveDisabledReason !== '' ? 'disabled title="' . e($approveDisabledReason) . '"' : '' ?>>
                    Approve this Version
                </button>
            <?php elseif ($isApprovedDocument): ?>
                <span class="project-document-chip chip-status status-approved">Approved</span>
            <?php endif; ?>
        </div>
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

