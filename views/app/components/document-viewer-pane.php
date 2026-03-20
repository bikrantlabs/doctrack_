<?php
/** @var array{id:int,project_id:int,title:string,current_version_id:int,created_at:string,project_role:string} $document */
/** @var array{id:int,document_id:int,version_number:int,file_path:string,file_type:string,uploaded_by:int,uploaded_by_name:string|null,change_summary:string|null,status:string|null,is_locked:int,created_at:string} $selectedVersion */
$fileUrl = url('/app/documents/file/' . (int)$selectedVersion['id']);
?>

<section class="document-viewer-pane"
         data-document-viewer="1"
         data-file-url="<?= e($fileUrl) ?>"
         data-file-type="<?= e((string)$selectedVersion['file_type']) ?>">
    <header class="document-viewer-header">
        <div class="document-viewer-title-block">
            <h1 class="document-viewer-title"><?= e((string)$document['title']) ?></h1>
            <div class="project-document-chips">
                <span class="project-document-chip chip-version">v<?= (int)$selectedVersion['version_number'] ?></span>
                <span class="project-document-chip chip-type"><?= strtoupper((string)$selectedVersion['file_type']) ?></span>
                <span class="project-document-chip chip-status status-<?= e((string)($selectedVersion['status'] ?? 'draft')) ?>">
                    <?= e((string)($selectedVersion['status'] ?? 'draft')) ?>
                </span>
            </div>
        </div>
        <a class="btn btn-outline btn-sm" href="<?= e($fileUrl . '?download=1') ?>" title="Download file">Download</a>
    </header>

    <div class="document-viewer-actions">
        <div class="document-viewer-controls" data-document-viewer-controls="1">
            <button class="btn btn-outline btn-sm" data-pdf-prev-btn="1" title="Previous page">←</button>
            <div class="pdf-page-info">
                <input type="number" class="pdf-page-input" data-pdf-page-input="1" min="1" value="1"/>
                <span class="pdf-page-count" data-pdf-page-count="1">/ 1</span>
            </div>
            <button class="btn btn-outline btn-sm" data-pdf-next-btn="1" title="Next page">→</button>
        </div>

    </div>

    <div class="document-viewer-body">
        <div class="document-viewer-loading" data-document-viewer-loading="1">Loading preview...</div>
        <div data-document-viewer-pdf="1" class="hidden"></div>
        <div class="document-viewer-docx hidden" data-document-viewer-docx="1"></div>
        <div class="document-viewer-error hidden" data-document-viewer-error="1"></div>
    </div>
</section>

