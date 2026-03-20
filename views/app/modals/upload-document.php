<?php
/** @var array{id:int, title:string, description:string|null, role:string, created_at:string, documentCount:int, memberCount:int} $project */
$uploadUrl = url('/app/projects/' . (int) $project['id'] . '/documents');
?>

<div class="modal-overlay hidden" id="upload-document-modal">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h2 class="modal-title">Upload Document</h2>
            <button type="button" class="modal-close" aria-label="Close modal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <form id="upload-document-form"
              class="modal-form"
              action="<?= e($uploadUrl) ?>"
              method="post"
              enctype="multipart/form-data"
              data-upload-url="<?= e($uploadUrl) ?>"
              novalidate>
            <div class="modal-body">
                <div class="form-group">
                    <label for="upload-document-title" class="form-label">Document Title <span class="required">*</span></label>
                    <input id="upload-document-title"
                           name="title"
                           type="text"
                           class="form-field"
                           placeholder="e.g. Final Proposal v1"
                           maxlength="255"
                           autocomplete="off"
                           required>
                </div>

                <div class="form-group">
                    <label class="form-label">File <span class="required">*</span></label>
                    <div class="native-dropzone" id="native-dropzone" tabindex="0" role="button" aria-label="Choose document file">
                        <input type="file"
                               id="upload-document-file"
                               name="document_file"
                               class="native-dropzone-input"
                               accept=".pdf,.docx,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                               required>
                        <div class="native-dropzone-content">
                            <p class="dropzone-title">Drop PDF or DOCX here, or click to browse</p>
                            <p class="dropzone-subtitle">Maximum file size: 25MB</p>
                        </div>
                        <div class="dropzone-selected-file hidden" id="dropzone-selected-file"></div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline modal-close">Cancel</button>
                <button type="submit" class="btn btn-primary" id="upload-document-submit-btn">Upload Document</button>
            </div>
        </form>
    </div>
</div>

