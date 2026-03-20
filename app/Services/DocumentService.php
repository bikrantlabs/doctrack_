<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\DocumentRepository;
use App\Repositories\ProjectRepository;
use Throwable;

final class DocumentService
{
    private const MAX_UPLOAD_SIZE_BYTES = 26214400; // 25MB
    private const ALLOWED_UPLOAD_ROLES = ['owner', 'editor'];
    private const ALLOWED_THREAD_CREATOR_ROLES = ['owner', 'reviewer'];
    private const ALLOWED_COMMENTER_ROLES = ['owner', 'editor', 'reviewer'];
    private const ALLOWED_RESOLVER_ROLES = ['owner', 'reviewer'];

    public function __construct(
        private readonly DocumentRepository $documents,
        private readonly ProjectRepository $projects
    ) {
    }

    /**
     * @return array<int, array{id:int,title:string,current_version_id:int,version_number:int,file_type:string,status:string,is_locked:int,uploaded_by_name:string|null,created_at:string}>
     */
    public function fetchProjectDocumentsForUser(int $projectId, int $userId): array
    {
        if ($projectId <= 0 || $userId <= 0) {
            return [];
        }

        return $this->documents->getProjectDocumentsForUser($projectId, $userId);
    }

    /** @return array{ok:bool,message:string} */
    public function uploadInitialDocument(int $projectId, int $actorUserId, string $title, array $file): array
    {
        if ($projectId <= 0 || $actorUserId <= 0) {
            return ['ok' => false, 'message' => 'Invalid request.'];
        }

        $role = $this->projects->getUserRoleInProject($projectId, $actorUserId);
        if (!in_array((string) $role, self::ALLOWED_UPLOAD_ROLES, true)) {
            return ['ok' => false, 'message' => 'Only owner or editor can upload documents.'];
        }

        $normalizedTitle = trim($title);
        if ($normalizedTitle === '') {
            return ['ok' => false, 'message' => 'Document title is required.'];
        }

        if (mb_strlen($normalizedTitle) > 255) {
            return ['ok' => false, 'message' => 'Document title cannot exceed 255 characters.'];
        }

        $fileError = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($fileError !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'message' => 'Please choose a PDF or DOCX file to upload.'];
        }

        $tmpPath = (string) ($file['tmp_name'] ?? '');
        if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
            return ['ok' => false, 'message' => 'Upload failed. Please try again.'];
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > self::MAX_UPLOAD_SIZE_BYTES) {
            return ['ok' => false, 'message' => 'File size must be between 1 byte and 25MB.'];
        }

        $originalName = (string) ($file['name'] ?? '');
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($extension, ['pdf', 'docx'], true)) {
            return ['ok' => false, 'message' => 'Only PDF and DOCX files are allowed.'];
        }

        $storageDirectory = BASE_PATH . '/storage/documents';
        if (!is_dir($storageDirectory) && !mkdir($storageDirectory, 0775, true) && !is_dir($storageDirectory)) {
            return ['ok' => false, 'message' => 'Could not prepare secure document storage.'];
        }

        $documentId = $this->generateId();
        $versionId = $this->generateId();
        $safeFileName = sprintf('%d_1_%d_%d.%s', $documentId, time(), random_int(1000, 9999), $extension);
        $targetPath = $storageDirectory . DIRECTORY_SEPARATOR . $safeFileName;

        if (!move_uploaded_file($tmpPath, $targetPath)) {
            return ['ok' => false, 'message' => 'Could not save uploaded file.'];
        }

        $storedFilePath = 'documents/' . $safeFileName;

        try {
            $this->documents->createDocumentWithInitialVersion(
                $documentId,
                $versionId,
                $projectId,
                $actorUserId,
                $normalizedTitle,
                $storedFilePath,
                $extension,
                'Initial upload.'
            );
        } catch (Throwable $exception) {
            if (is_file($targetPath)) {
                @unlink($targetPath);
            }

            return ['ok' => false, 'message' => 'Could not create document record.'];
        }

        return ['ok' => true, 'message' => 'Document uploaded and Version 1 created.'];
    }

    /**
     * @return array{document:array{id:int,project_id:int,title:string,current_version_id:int,created_at:string,project_role:string}, selectedVersion:array{id:int,document_id:int,version_number:int,file_path:string,file_type:string,uploaded_by:int,uploaded_by_name:string|null,change_summary:string|null,status:string|null,is_locked:int,created_at:string}, versions:array<int, array{id:int,document_id:int,version_number:int,file_path:string,file_type:string,uploaded_by:int,uploaded_by_name:string|null,change_summary:string|null,status:string|null,is_locked:int,created_at:string}>, threads:array<int, array{id:int,title:string,created_by:int,created_by_name:string|null,created_at:string,status:string,selected_version_status:string,open_version_numbers:array<int,int>,comments:array<int, array{id:int,review_thread_id:int,document_version_id:int,version_number:int,reviewer_id:int,reviewer_name:string|null,page_number:int,comment:string,created_at:string}>}>}|null
     */
    public function fetchDocumentDetailForUser(int $projectId, int $documentId, int $userId, ?int $requestedVersion): ?array
    {
        if ($projectId <= 0 || $documentId <= 0 || $userId <= 0) {
            return null;
        }

        $document = $this->documents->getDocumentDetailForUser($projectId, $documentId, $userId);
        if ($document === null) {
            return null;
        }

        $versions = $this->documents->getDocumentVersionsForUser($projectId, $documentId, $userId);
        if ($versions === []) {
            return null;
        }

        $selectedVersion = null;

        if ($requestedVersion !== null && $requestedVersion > 0) {
            foreach ($versions as $version) {
                if ((int) $version['version_number'] === $requestedVersion) {
                    $selectedVersion = $version;
                    break;
                }
            }
        }

        if ($selectedVersion === null) {
            $currentVersionId = (int) $document['current_version_id'];
            foreach ($versions as $version) {
                if ((int) $version['id'] === $currentVersionId) {
                    $selectedVersion = $version;
                    break;
                }
            }
        }

        if ($selectedVersion === null) {
            $selectedVersion = $versions[0];
        }

        $threads = $this->documents->getReviewThreadsForUser($projectId, $documentId, $userId);
        $comments = $this->documents->getReviewCommentsForDocumentForUser($projectId, $documentId, $userId);
        $selectedVersionStatusMap = $this->documents->getThreadStatusMapForVersionForUser(
            $projectId,
            $documentId,
            $userId,
            (int) $selectedVersion['id']
        );

        $commentsByThreadId = [];
        foreach ($comments as $comment) {
            $threadId = (int) $comment['review_thread_id'];
            if (!isset($commentsByThreadId[$threadId])) {
                $commentsByThreadId[$threadId] = [];
            }

            $commentsByThreadId[$threadId][] = $comment;
        }

        foreach ($threads as $index => $thread) {
            $threadId = (int) $thread['id'];
            $threads[$index]['comments'] = $commentsByThreadId[$threadId] ?? [];
            $threads[$index]['selected_version_status'] = $selectedVersionStatusMap[$threadId] ?? 'open';
        }

        return [
            'document' => $document,
            'selectedVersion' => $selectedVersion,
            'versions' => $versions,
            'threads' => $threads,
        ];
    }

    /** @return array{ok:bool,message:string} */
    public function createReviewThreadForUser(
        int $projectId,
        int $documentId,
        int $actorUserId,
        string $title,
        string $comment,
        int $pageNumber,
        int $versionId
    ): array {
        if ($projectId <= 0 || $documentId <= 0 || $actorUserId <= 0 || $versionId <= 0) {
            return ['ok' => false, 'message' => 'Invalid request.'];
        }

        $role = $this->projects->getUserRoleInProject($projectId, $actorUserId);
        if (!in_array((string) $role, self::ALLOWED_THREAD_CREATOR_ROLES, true)) {
            return ['ok' => false, 'message' => 'Only owner or reviewer can create review threads.'];
        }

        $document = $this->documents->getDocumentDetailForUser($projectId, $documentId, $actorUserId);
        if ($document === null) {
            return ['ok' => false, 'message' => 'Document not found.'];
        }

        $normalizedTitle = trim($title);
        if ($normalizedTitle === '') {
            return ['ok' => false, 'message' => 'Thread title is required.'];
        }

        if (mb_strlen($normalizedTitle) > 255) {
            return ['ok' => false, 'message' => 'Thread title cannot exceed 255 characters.'];
        }

        $normalizedComment = trim($comment);
        if ($normalizedComment === '') {
            return ['ok' => false, 'message' => 'First comment is required.'];
        }

        if ($pageNumber <= 0) {
            return ['ok' => false, 'message' => 'Page number must be greater than zero.'];
        }

        $version = $this->documents->getDocumentVersionByIdForUser($versionId, $actorUserId);
        if ($version === null || (int) $version['document_id'] !== $documentId) {
            return ['ok' => false, 'message' => 'Selected version was not found.'];
        }

        $threadId = $this->generateId();
        $commentId = $this->generateId();

        try {
            $this->documents->createReviewThreadWithInitialComment(
                $threadId,
                $commentId,
                $documentId,
                $actorUserId,
                $normalizedTitle,
                $normalizedComment,
                $pageNumber,
                (int) $version['id']
            );
        } catch (Throwable $exception) {
            return ['ok' => false, 'message' => 'Could not create review thread.'];
        }

        return ['ok' => true, 'message' => 'Review thread created.'];
    }

    /** @return array{ok:bool,message:string} */
    public function addReviewCommentForUser(
        int $projectId,
        int $documentId,
        int $threadId,
        int $actorUserId,
        string $comment,
        int $pageNumber,
        int $versionId
    ): array {
        if ($projectId <= 0 || $documentId <= 0 || $threadId <= 0 || $actorUserId <= 0 || $versionId <= 0) {
            return ['ok' => false, 'message' => 'Invalid request.'];
        }

        $role = $this->projects->getUserRoleInProject($projectId, $actorUserId);
        if (!in_array((string) $role, self::ALLOWED_COMMENTER_ROLES, true)) {
            return ['ok' => false, 'message' => 'Only owner, editor, or reviewer can comment.'];
        }

        $thread = $this->documents->getReviewThreadForUser($projectId, $documentId, $threadId, $actorUserId);
        if ($thread === null) {
            return ['ok' => false, 'message' => 'Review thread not found.'];
        }

        $version = $this->documents->getDocumentVersionByIdForUser($versionId, $actorUserId);
        if ($version === null || (int) $version['document_id'] !== $documentId) {
            return ['ok' => false, 'message' => 'Selected version was not found.'];
        }

        $normalizedComment = trim($comment);
        if ($normalizedComment === '') {
            return ['ok' => false, 'message' => 'Comment is required.'];
        }

        if ($pageNumber <= 0) {
            return ['ok' => false, 'message' => 'Page number must be greater than zero.'];
        }

        try {
            $this->documents->createReviewComment(
                $this->generateId(),
                $threadId,
                $versionId,
                $actorUserId,
                $pageNumber,
                $normalizedComment
            );
            $this->documents->upsertReviewStatusOpen($threadId, $versionId);
        } catch (Throwable $exception) {
            return ['ok' => false, 'message' => 'Could not add comment.'];
        }

        return ['ok' => true, 'message' => 'Comment added.'];
    }

    /** @return array{ok:bool,message:string} */
    public function resolveReviewThreadForUser(
        int $projectId,
        int $documentId,
        int $threadId,
        int $actorUserId,
        int $versionId
    ): array {
        if ($projectId <= 0 || $documentId <= 0 || $threadId <= 0 || $actorUserId <= 0 || $versionId <= 0) {
            return ['ok' => false, 'message' => 'Invalid request.'];
        }

        $role = $this->projects->getUserRoleInProject($projectId, $actorUserId);
        if (!in_array((string) $role, self::ALLOWED_RESOLVER_ROLES, true)) {
            return ['ok' => false, 'message' => 'Only owner or reviewer can resolve threads.'];
        }

        $thread = $this->documents->getReviewThreadForUser($projectId, $documentId, $threadId, $actorUserId);
        if ($thread === null) {
            return ['ok' => false, 'message' => 'Review thread not found.'];
        }

        $version = $this->documents->getDocumentVersionByIdForUser($versionId, $actorUserId);
        if ($version === null || (int) $version['document_id'] !== $documentId) {
            return ['ok' => false, 'message' => 'Selected version was not found.'];
        }

        try {
            $this->documents->resolveReviewThreadForVersion($threadId, $versionId, $actorUserId);
        } catch (Throwable $exception) {
            return ['ok' => false, 'message' => 'Could not resolve thread.'];
        }

        return ['ok' => true, 'message' => 'Thread marked as resolved.'];
    }

    /**
     * @return array{versionId:int,fileType:string,versionNumber:int,fileName:string,absolutePath:string}|null
     */
    public function getVersionFileForUser(int $versionId, int $userId): ?array
    {
        if ($versionId <= 0 || $userId <= 0) {
            return null;
        }

        $version = $this->documents->getDocumentVersionByIdForUser($versionId, $userId);
        if ($version === null) {
            return null;
        }

        $relativePath = trim((string) $version['file_path']);
        if ($relativePath === '' || str_contains($relativePath, '..')) {
            return null;
        }

        $storageRoot = BASE_PATH . '/storage';
        $absolutePath = $storageRoot . '/' . ltrim($relativePath, '/');

        $realStorageRoot = realpath($storageRoot);
        $realFilePath = realpath($absolutePath);

        if ($realStorageRoot === false || $realFilePath === false) {
            return null;
        }

        if (!str_starts_with($realFilePath, $realStorageRoot) || !is_file($realFilePath)) {
            return null;
        }

        $fileType = (string) $version['file_type'];
        $versionNumber = (int) $version['version_number'];
        $fileName = sprintf('document_v%d.%s', $versionNumber, $fileType);

        return [
            'versionId' => (int) $version['id'],
            'fileType' => $fileType,
            'versionNumber' => $versionNumber,
            'fileName' => $fileName,
            'absolutePath' => $realFilePath,
        ];
    }

    private function generateId(): int
    {
        return random_int(100000000000, 9000000000000000000);
    }
}

