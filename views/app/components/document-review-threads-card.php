<?php
/** @var array{id:int,project_id:int,title:string,current_version_id:int,created_at:string,project_role:string} $document */
/** @var array{id:int,document_id:int,version_number:int,file_path:string,file_type:string,uploaded_by:int,uploaded_by_name:string|null,change_summary:string|null,status:string|null,is_locked:int,created_at:string} $selectedVersion */
/** @var array<int, array{id:int,document_id:int,version_number:int,file_path:string,file_type:string,uploaded_by:int,uploaded_by_name:string|null,change_summary:string|null,status:string|null,is_locked:int,created_at:string}> $versions */
/** @var array<int, array{id:int,title:string,created_by:int,created_by_name:string|null,created_at:string,status:string,selected_version_status:string,open_version_numbers:array<int,int>,comments:array<int, array{id:int,review_thread_id:int,document_version_id:int,version_number:int,reviewer_id:int,reviewer_name:string|null,page_number:int,comment:string,created_at:string}>}> $threads */

$openThreadCount = 0;
foreach ($threads as $thread) {
    if ((string)$thread['status'] === 'open') {
        $openThreadCount++;
    }
}

$canCreateThread = in_array((string)$document['project_role'], ['owner', 'reviewer'], true);
$canComment = in_array((string)$document['project_role'], ['owner', 'editor', 'reviewer'], true);
$canResolve = in_array((string)$document['project_role'], ['owner', 'reviewer'], true);
$createThreadUrl = url('/app/projects/' . (int)$document['project_id'] . '/' . (int)$document['id'] . '/threads');
$commentUrlTemplate = url('/app/projects/' . (int)$document['project_id'] . '/' . (int)$document['id'] . '/threads/__THREAD_ID__/comments');
$resolveUrlTemplate = url('/app/projects/' . (int)$document['project_id'] . '/' . (int)$document['id'] . '/threads/__THREAD_ID__/resolve');
?>

<section class="document-detail-card review-threads-card"
         data-review-threads-root="1"
         data-create-thread-url="<?= e($createThreadUrl) ?>"
         data-comment-url-template="<?= e($commentUrlTemplate) ?>"
         data-resolve-url-template="<?= e($resolveUrlTemplate) ?>"
         data-selected-version-id="<?= e((string)$selectedVersion['id']) ?>">

    <!-- ========== LIST MODE HEADER ========== -->
    <div class="thread-heading-row document-card-heading-row" data-thread-list-header="1">
        <div class="thread-heading-text">
            <h2>Review Threads</h2>
            <span class="project-document-chip chip-count thread-open-subtitle"
                  data-thread-open-count="1"><?= (int)$openThreadCount ?> open</span>
        </div>
        <?php if ($canCreateThread): ?>
            <button type="button" class="btn btn-primary btn-sm" data-thread-create-toggle="1">
                New Thread
            </button>
        <?php endif; ?>
    </div>

    <!-- ========== DETAIL/CREATE MODE HEADER ========== -->
    <div class="thread-mode-header hidden" data-thread-mode-header="1">
        <button type="button" class="btn btn-outline btn-sm thread-back-btn" data-thread-mode-back="1">
            ← Back
        </button>
        <div class="thread-mode-title-row">
            <h2 class="thread-mode-title" data-thread-mode-title="1">Review Thread</h2>
            <span class="project-document-chip chip-status status-open hidden"
                  data-thread-mode-status="1">open</span>
        </div>
        <p class="thread-mode-meta hidden" data-thread-mode-meta="1"></p>
    </div>

    <!-- ========== CREATE THREAD FORM ========== -->
    <?php if ($canCreateThread): ?>
        <form class="thread-create-form hidden thread-mode-panel" data-thread-create-form="1" novalidate>
            <div class="form-group">
                <label class="form-label" for="thread-title-input">Thread Title</label>
                <input id="thread-title-input"
                       class="form-field"
                       type="text"
                       maxlength="255"
                       name="title"
                       placeholder="Describe the issue to review"
                       required>
            </div>
            <div class="form-group">
                <label class="form-label" for="thread-comment-input">First Comment</label>
                <textarea id="thread-comment-input"
                          class="form-field thread-comment-input"
                          name="comment"
                          rows="4"
                          placeholder="Write the first review comment"
                          required></textarea>
            </div>
            <div class="thread-create-form-row">
                <div class="form-group">
                    <label class="form-label" for="thread-page-number-input">Page Number</label>
                    <input id="thread-page-number-input"
                           class="form-field"
                           type="number"
                           name="page_number"
                           min="1"
                           step="1"
                           value="1"
                           required>
                </div>
                <div class="thread-create-form-buttons">
                    <button type="button" class="btn btn-outline btn-sm" data-thread-create-cancel="1">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm" data-thread-create-submit="1">Create</button>
                </div>
            </div>
        </form>
    <?php endif; ?>

    <!-- ========== THREAD LIST VIEW ========== -->
    <div data-thread-list-view="1">
        <div class="thread-filter-row" aria-label="Thread filters">
            <label class="form-label sr-only" for="thread-status-filter-select">Filter by status</label>
            <select id="thread-status-filter-select"
                    class="filter-select thread-status-select"
                    data-thread-status-select="1">
                <option value="all">All threads</option>
                <option value="open">Open</option>
                <option value="resolved">Resolved</option>
            </select>
        </div>

        <div class="thread-list" data-thread-list="1">
            <?php foreach ($threads as $thread): ?>
                <?php
                $openVersions = $thread['open_version_numbers'];
                $openVersionsText = $openVersions === [] ? 'None' : 'v' . implode(', v', $openVersions);
                $commentCount = count($thread['comments']);
                ?>
                <article class="thread-item"
                         data-thread-item="1"
                         data-thread-id="<?= e((string)$thread['id']) ?>"
                         data-thread-status="<?= e((string)$thread['status']) ?>">
                    <button type="button"
                            class="thread-item-trigger"
                            data-thread-open-btn="<?= e((string)$thread['id']) ?>"
                            data-thread-title="<?= e((string)$thread['title']) ?>"
                            data-thread-selected-status="<?= e((string)$thread['selected_version_status']) ?>"
                            data-thread-created-by="<?= e((string)($thread['created_by_name'] ?? 'Unknown')) ?>">
                        <span class="thread-item-header">
                            <span class="thread-item-title"><?= e((string)$thread['title']) ?></span>
                            <span class="project-document-chip chip-status status-<?= e((string)$thread['status']) ?>">
                                <?= e((string)$thread['status']) ?>
                            </span>
                        </span>
                        <span class="thread-item-footer">
                            <span class="thread-item-meta">
                                By <?= e((string)($thread['created_by_name'] ?? 'Unknown')) ?>
                                · <?= $commentCount ?> <?= $commentCount === 1 ? 'comment' : 'comments' ?>
                            </span>
                            <span class="thread-item-open-versions">Open on: <?= e($openVersionsText) ?></span>
                        </span>
                    </button>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="thread-empty-state <?= $threads === [] ? '' : 'hidden' ?>" data-thread-empty-state="1">
            <p>No review threads yet.</p>
            <p class="thread-empty-note">Create one to start a structured review discussion.</p>
        </div>

        <div class="thread-empty-state hidden" data-thread-filter-empty="1">
            <p>No threads match this filter.</p>
            <p class="thread-empty-note">Try switching to "All threads".</p>
        </div>
    </div>

    <!-- ========== THREAD DETAIL VIEW ========== -->
    <div class="thread-detail-view hidden thread-mode-panel" data-thread-detail-view="1">
        <?php foreach ($threads as $thread): ?>
            <?php $isSelectedVersionResolved = (string)$thread['selected_version_status'] === 'resolved'; ?>
            <article class="thread-detail-panel hidden"
                     data-thread-detail-panel="<?= e((string)$thread['id']) ?>"
                     data-thread-selected-status="<?= e((string)$thread['selected_version_status']) ?>">

                <!-- Resolve action bar -->
                <?php if ($canResolve && !$isSelectedVersionResolved): ?>
                    <div class="thread-detail-actions">
                        <button type="button"
                                class="btn btn-primary btn-sm"
                                data-thread-resolve-btn="<?= e((string)$thread['id']) ?>">
                            Mark as Resolved
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Comments -->
                <div class="thread-comments-list">
                    <?php if ($thread['comments'] === []): ?>
                        <p class="thread-comments-empty">No comments yet on this thread.</p>
                    <?php else: ?>
                        <?php foreach ($thread['comments'] as $comment): ?>
                            <?php $isOtherVersion = (int)$comment['document_version_id'] !== (int)$selectedVersion['id']; ?>
                            <article class="thread-comment-item <?= $isOtherVersion ? 'is-other-version' : '' ?>">
                                <div class="thread-comment-meta">
                                    <span class="thread-comment-author"><?= e((string)($comment['reviewer_name'] ?? 'Unknown')) ?></span>
                                    <span class="thread-comment-chip">v<?= (int)$comment['version_number'] ?></span>
                                    <button type="button"
                                            class="thread-comment-chip thread-comment-page-chip"
                                            data-thread-page-jump="<?= (int)$comment['page_number'] ?>"
                                            title="Go to page <?= (int)$comment['page_number'] ?> in document viewer">
                                        Page <?= (int)$comment['page_number'] ?>
                                    </button>
                                    <span class="thread-comment-time"><?= e((string)$comment['created_at']) ?></span>
                                </div>
                                <p class="thread-comment-body"><?= nl2br(e((string)$comment['comment'])) ?></p>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Add Comment form -->
                <?php if ($canComment && !$isSelectedVersionResolved): ?>
                    <form class="thread-comment-form" data-thread-comment-form="<?= e((string)$thread['id']) ?>"
                          novalidate>
                        <div class="form-group">
                            <label class="form-label" for="thread-comment-new-<?= e((string)$thread['id']) ?>">Add
                                Comment</label>
                            <textarea id="thread-comment-new-<?= e((string)$thread['id']) ?>"
                                      class="form-field thread-comment-input"
                                      name="comment"
                                      rows="3"
                                      placeholder="Write your review comment"
                                      required></textarea>
                        </div>
                        <div class="thread-comment-controls">
                            <div class="form-group">
                                <label class="form-label"
                                       for="thread-page-mode-<?= e((string)$thread['id']) ?>">Page</label>
                                <select id="thread-page-mode-<?= e((string)$thread['id']) ?>"
                                        class="filter-select"
                                        data-thread-page-mode="<?= e((string)$thread['id']) ?>">
                                    <option value="current">Current Page</option>
                                    <option value="custom">Specify Page</option>
                                </select>
                            </div>
                            <div class="form-group hidden"
                                 data-thread-custom-page-wrap="<?= e((string)$thread['id']) ?>">
                                <label class="form-label" for="thread-custom-page-<?= e((string)$thread['id']) ?>">Page
                                    No.</label>
                                <input id="thread-custom-page-<?= e((string)$thread['id']) ?>"
                                       class="form-field"
                                       type="number"
                                       min="1"
                                       step="1"
                                       value="1"
                                       data-thread-custom-page="<?= e((string)$thread['id']) ?>">
                            </div>
                            <button type="submit"
                                    class="btn btn-primary btn-sm"
                                    data-thread-comment-submit="<?= e((string)$thread['id']) ?>">
                                Add Comment
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>