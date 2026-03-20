<?php
/** @var array{id:int,project_id:int,title:string,current_version_id:int,created_at:string,project_role:string} $document */
/** @var array{id:int,document_id:int,version_number:int,file_path:string,file_type:string,uploaded_by:int,uploaded_by_name:string|null,change_summary:string|null,status:string|null,is_locked:int,created_at:string} $selectedVersion */
/** @var array<int, array{id:int,document_id:int,version_number:int,file_path:string,file_type:string,uploaded_by:int,uploaded_by_name:string|null,change_summary:string|null,status:string|null,is_locked:int,created_at:string}> $versions */
/** @var array<int, array{id:int,title:string,created_by:int,created_by_name:string|null,created_at:string,status:string,selected_version_status:string,open_version_numbers:array<int,int>,comments:array<int, array{id:int,review_thread_id:int,document_version_id:int,version_number:int,reviewer_id:int,reviewer_name:string|null,page_number:int,comment:string,created_at:string}>}> $threads */
?>

<div class="document-page-layout">
    <?php require BASE_PATH . '/views/app/components/document-viewer-pane.php'; ?>
    <?php require BASE_PATH . '/views/app/components/document-detail-sidebar.php'; ?>
</div>

<script src="<?= e(url('/js/libraries/mammoth.js')) ?>" defer></script>
<script src="<?= e(url('/js/libraries/pdfjs.js')) ?>" type="module"></script>
<script src="<?= e(url('/js/app/document-viewer.js')) ?>" defer></script>
<script src="<?= e(url('/js/app/document-review-threads.js')) ?>" defer></script>

