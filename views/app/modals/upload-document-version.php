<?php
/** @var array{id:int,project_id:int,title:string,current_version_id:int,created_at:string,project_role:string} $document */
/** @var array{id:int,document_id:int,version_number:int,file_path:string,file_type:string,uploaded_by:int,uploaded_by_name:string|null,change_summary:string|null,status:string|null,is_locked:int,created_at:string} $selectedVersion */
/** @var array<int, array{id:int,title:string,created_by:int,created_by_name:string|null,created_at:string,status:string,selected_version_status:string,open_version_numbers:array<int,int>,comments:array<int, array{id:int,review_thread_id:int,document_version_id:int,version_number:int,reviewer_id:int,reviewer_name:string|null,page_number:int,comment:string,created_at:string}>}> $threads */
$uploadVersionUrl = url('/app/projects/' . (int) $document['project_id'] . '/' . (int) $document['id'] . '/versions');
$documentUrl = url('/app/projects/' . (int) $document['project_id'] . '/' . (int) $document['id']);
$openThreads = array_values(array_filter($threads, static function (array $thread): bool {
    return (string) ($thread['selected_version_status'] ?? 'open') === 'open';
}));
?>

<div class="modal-overlay hidden" id="upload-document-version-modal">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h2 class="modal-title">Upload New Version</h2>
            <button type="button" class="modal-close" aria-label="Close modal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <form id="upload-document-version-form"
              class="modal-form"
              action="<?= e($uploadVersionUrl) ?>"
              method="post"
              enctype="multipart/form-data"
              data-upload-url="<?= e($uploadVersionUrl) ?>"
              data-document-url="<?= e($documentUrl) ?>"
              novalidate>
            <input type="hidden" name="base_version_id" value="<?= e((string) $selectedVersion['id']) ?>">
            <div class="modal-body">
                <div class="form-group">
                    <label for="upload-version-summary" class="form-label">Change Summary</label>
                    <textarea id="upload-version-summary"
                              name="change_summary"
                              class="form-textarea"
                              placeholder="What changed in this version?"
                              maxlength="1000"
                              rows="4"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">File <span class="required">*</span></label>
                    <div class="native-dropzone" id="version-native-dropzone" tabindex="0" role="button" aria-label="Choose new version file">
                        <input type="file"
                               id="upload-document-version-file"
                               name="version_file"
                               class="native-dropzone-input"
                               accept=".pdf,.docx,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                               required>
                        <div class="native-dropzone-content">
                            <p class="dropzone-title">Drop updated PDF or DOCX here, or click to browse</p>
                            <p class="dropzone-subtitle">Maximum file size: 25MB</p>
                        </div>
                        <div class="dropzone-selected-file hidden" id="version-dropzone-selected-file"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Threads Fixed in This Version</label>
                    <?php if ($openThreads === []): ?>
                        <p class="version-thread-empty">No open review threads on the current version.</p>
                    <?php else: ?>
                        <div class="version-thread-checklist">
                            <?php foreach ($openThreads as $thread): ?>
                                <label class="version-thread-option">
                                    <input type="checkbox"
                                           name="review_thread_ids[]"
                                           value="<?= e((string) $thread['id']) ?>">
                                    <span class="version-thread-checkmark" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                            <polyline points="20 6 9 17 4 12"/>
                                        </svg>
                                    </span>
                                    <span class="version-thread-option-text">
                                        <strong><?= e((string) $thread['title']) ?></strong>
                                        <span>Mark for reviewer verification on the new version</span>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline modal-close">Cancel</button>
                <button type="submit" class="btn btn-primary" id="upload-document-version-submit-btn">Upload New Version</button>
            </div>
        </form>
    </div>
</div>
