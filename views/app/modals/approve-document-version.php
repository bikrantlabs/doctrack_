<?php
/** @var array{id:int,project_id:int,title:string,current_version_id:int,created_at:string,project_role:string,is_approved?:bool,approved_version_number?:int|null} $document */
/** @var array{id:int,document_id:int,version_number:int,file_path:string,file_type:string,uploaded_by:int,uploaded_by_name:string|null,change_summary:string|null,status:string|null,is_locked:int,created_at:string} $selectedVersion */
$approveVersionUrl = url('/app/projects/' . (int) $document['project_id'] . '/' . (int) $document['id'] . '/approve');
$documentUrl = url('/app/projects/' . (int) $document['project_id'] . '/' . (int) $document['id']);
?>
<div class="modal-overlay hidden" id="approve-version-modal">
    <div class="modal">
        <div class="modal-header">
            <h2 class="modal-title">Approve Version <?= (int) $selectedVersion['version_number'] ?></h2>
            <button type="button" class="modal-close" aria-label="Close modal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <form id="approve-version-form"
              class="modal-form"
              action="<?= e($approveVersionUrl) ?>"
              method="post"
              data-approve-url="<?= e($approveVersionUrl) ?>"
              data-document-url="<?= e($documentUrl) ?>"
              novalidate>
            <input type="hidden" name="version_id" value="<?= e((string) $selectedVersion['id']) ?>">
            <div class="modal-body">
                <div class="approval-warning">
                    <h3>Approve this document version?</h3>
                    <p>After approval, this document becomes view-only. New versions, new review threads, and further thread updates will be blocked.</p>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="modal-close" style="margin-right:16px;">Cancel</button>
                <button type="submit" class="btn btn-primary" id="approve-version-submit-btn">
                    Confirm Approval
                </button>
            </div>
        </form>
    </div>
</div>
