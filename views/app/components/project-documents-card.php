<?php
/** @var array{id:int, title:string, description:string|null, role:string, created_at:string, documentCount:int, memberCount:int} $project */
/** @var array<int, array{id:int,title:string,current_version_id:int,version_number:int,file_type:string,status:string,is_locked:int,uploaded_by_name:string|null,created_at:string}> $documents */
?>

<section class="project-documents-card">
    <div class="project-documents-header">
        <h2>Documents</h2>
        <p><?= count($documents) ?> <?= count($documents) === 1 ? 'document' : 'documents' ?></p>
    </div>

    <?php if ($documents === []): ?>
        <div class="project-documents-empty">
            <p>No documents yet. Upload your first PDF or DOCX to start review tracking.</p>
        </div>
    <?php else: ?>
        <div class="project-documents-grid">
            <?php foreach ($documents as $document): ?>
                <?php
                $status = (string) $document['status'];
                $versionNumber = (int) $document['version_number'];
                ?>
                <a class="project-document-link" href="<?= e(url('/app/projects/' . (int) $project['id'] . '/' . (int) $document['id'])) ?>">
                    <article class="project-document-card">
                    <div class="project-document-card-header">
                        <div class="project-document-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                            </svg>
                        </div>
                        <span class="project-document-chip chip-status status-<?= e($status) ?>">
                            <?= e($status) ?>
                        </span>
                    </div>

                    <div class="project-document-main">
                        <h3><?= e((string) $document['title']) ?></h3>
                        <p class="project-document-chips">
                            <span class="project-document-chip chip-version">v<?= $versionNumber ?></span>
                            <span class="project-document-chip chip-type"><?= strtoupper((string) $document['file_type']) ?></span>
                            <span class="project-document-chip chip-count"><?= $versionNumber ?> <?= $versionNumber === 1 ? 'version' : 'versions' ?></span>
                        </p>
                    </div>

                    <div class="project-document-meta">
                        <span>Uploaded by <?= e((string) ($document['uploaded_by_name'] ?? 'Unknown')) ?></span>
                    </div>
                    </article>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

