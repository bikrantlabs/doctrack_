<?php
/** @var array{id:int,project_id:int,title:string,current_version_id:int,created_at:string,project_role:string} $document */
/** @var array{id:int,document_id:int,version_number:int,file_path:string,file_type:string,uploaded_by:int,uploaded_by_name:string|null,change_summary:string|null,status:string|null,is_locked:int,created_at:string} $selectedVersion */
/** @var array<int, array{id:int,document_id:int,version_number:int,file_path:string,file_type:string,uploaded_by:int,uploaded_by_name:string|null,change_summary:string|null,status:string|null,is_locked:int,created_at:string}> $versions */
?>

<div class="document-page-layout">
    <?php require BASE_PATH . '/views/app/components/document-viewer-pane.php'; ?>
    <?php require BASE_PATH . '/views/app/components/document-detail-sidebar.php'; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/mammoth@1.8.0/mammoth.browser.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.4.168/pdf.min.mjs" type="module"></script>
<script src="<?= e(url('/js/app/document-viewer.js')) ?>" defer></script>

