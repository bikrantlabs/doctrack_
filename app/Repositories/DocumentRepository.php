<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use Throwable;

final class DocumentRepository
{
    /**
     * @return array<int, array{id:int,title:string,current_version_id:int,version_number:int,file_type:string,status:string,is_locked:int,uploaded_by_name:string|null,created_at:string}>
     */
    public function getProjectDocumentsForUser(int $projectId, int $userId): array
    {
        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'SELECT d.id,
                    d.title,
                    d.current_version_id,
                    dv.version_number,
                    dv.file_type,
                    COALESCE(dv.status, \'draft\') AS status,
                    dv.is_locked,
                    d.created_at,
                    uploader.fullname AS uploaded_by_name
             FROM documents d
             INNER JOIN document_versions dv ON dv.id = d.current_version_id
             LEFT JOIN users uploader ON uploader.id = dv.uploaded_by
             WHERE d.project_id = :project_id
               AND EXISTS (
                    SELECT 1
                    FROM user_projects up
                    WHERE up.project_id = d.project_id
                      AND up.user_id = :user_id
               )
             ORDER BY d.created_at DESC'
        );

        $statement->execute([
            'project_id' => $projectId,
            'user_id' => $userId,
        ]);

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function createDocumentWithInitialVersion(
        int $documentId,
        int $versionId,
        int $projectId,
        int $createdBy,
        string $title,
        string $storedFilePath,
        string $fileType,
        ?string $changeSummary = null
    ): void {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $documentStatement = $pdo->prepare(
                'INSERT INTO documents (id, project_id, title, created_by, current_version_id)
                 VALUES (:id, :project_id, :title, :created_by, :current_version_id)'
            );

            $documentStatement->execute([
                'id' => $documentId,
                'project_id' => $projectId,
                'title' => $title,
                'created_by' => $createdBy,
                'current_version_id' => $versionId,
            ]);

            $versionStatement = $pdo->prepare(
                'INSERT INTO document_versions (
                    id,
                    document_id,
                    version_number,
                    file_path,
                    file_type,
                    uploaded_by,
                    change_summary,
                    status,
                    is_locked
                 ) VALUES (
                    :id,
                    :document_id,
                    :version_number,
                    :file_path,
                    :file_type,
                    :uploaded_by,
                    :change_summary,
                    :status,
                    :is_locked
                 )'
            );

            $versionStatement->execute([
                'id' => $versionId,
                'document_id' => $documentId,
                'version_number' => 1,
                'file_path' => $storedFilePath,
                'file_type' => $fileType,
                'uploaded_by' => $createdBy,
                'change_summary' => $changeSummary,
                'status' => 'draft',
                'is_locked' => 0,
            ]);

            $pdo->commit();
        } catch (Throwable $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }

    /**
     * @return array{id:int,project_id:int,title:string,current_version_id:int,created_at:string,project_role:string}|null
     */
    public function getDocumentDetailForUser(int $projectId, int $documentId, int $userId): ?array
    {
        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'SELECT d.id,
                    d.project_id,
                    d.title,
                    d.current_version_id,
                    d.created_at,
                    up.role AS project_role
             FROM documents d
             INNER JOIN user_projects up ON up.project_id = d.project_id
             WHERE d.project_id = :project_id
               AND d.id = :document_id
               AND up.user_id = :user_id
             LIMIT 1'
        );

        $statement->execute([
            'project_id' => $projectId,
            'document_id' => $documentId,
            'user_id' => $userId,
        ]);

        $document = $statement->fetch(\PDO::FETCH_ASSOC);
        return $document ?: null;
    }

    /**
     * @return array<int, array{id:int,document_id:int,version_number:int,file_path:string,file_type:string,uploaded_by:int,uploaded_by_name:string|null,change_summary:string|null,status:string|null,is_locked:int,created_at:string}>
     */
    public function getDocumentVersionsForUser(int $projectId, int $documentId, int $userId): array
    {
        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'SELECT dv.id,
                    dv.document_id,
                    dv.version_number,
                    dv.file_path,
                    dv.file_type,
                    dv.uploaded_by,
                    uploader.fullname AS uploaded_by_name,
                    dv.change_summary,
                    dv.status,
                    dv.is_locked,
                    dv.created_at
             FROM document_versions dv
             INNER JOIN documents d ON d.id = dv.document_id
             LEFT JOIN users uploader ON uploader.id = dv.uploaded_by
             WHERE d.project_id = :project_id
               AND d.id = :document_id
               AND EXISTS (
                    SELECT 1
                    FROM user_projects up
                    WHERE up.project_id = d.project_id
                      AND up.user_id = :user_id
               )
             ORDER BY dv.version_number DESC'
        );

        $statement->execute([
            'project_id' => $projectId,
            'document_id' => $documentId,
            'user_id' => $userId,
        ]);

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @return array{id:int,document_id:int,version_number:int,file_path:string,file_type:string,uploaded_by:int,uploaded_by_name:string|null,change_summary:string|null,status:string|null,is_locked:int,created_at:string}|null
     */
    public function getDocumentVersionByIdForUser(int $versionId, int $userId): ?array
    {
        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'SELECT dv.id,
                    dv.document_id,
                    dv.version_number,
                    dv.file_path,
                    dv.file_type,
                    dv.uploaded_by,
                    uploader.fullname AS uploaded_by_name,
                    dv.change_summary,
                    dv.status,
                    dv.is_locked,
                    dv.created_at
             FROM document_versions dv
             INNER JOIN documents d ON d.id = dv.document_id
             LEFT JOIN users uploader ON uploader.id = dv.uploaded_by
             WHERE dv.id = :version_id
               AND EXISTS (
                    SELECT 1
                    FROM user_projects up
                    WHERE up.project_id = d.project_id
                      AND up.user_id = :user_id
               )
             LIMIT 1'
        );

        $statement->execute([
            'version_id' => $versionId,
            'user_id' => $userId,
        ]);

        $version = $statement->fetch(\PDO::FETCH_ASSOC);
        return $version ?: null;
    }

    /**
     * @return array<int, array{id:int,title:string,created_by:int,created_by_name:string|null,created_at:string,status:string,open_version_numbers:array<int,int>}>
     */
    public function getReviewThreadsForUser(int $projectId, int $documentId, int $userId): array
    {
        $pdo = Database::connection();

        $threadStatement = $pdo->prepare(
            'SELECT rt.id,
                    rt.title,
                    rt.created_by,
                    creator.fullname AS created_by_name,
                    rt.created_at,
                    COALESCE(SUM(CASE WHEN rs.status = \'open\' THEN 1 ELSE 0 END), 0) AS open_count
             FROM review_threads rt
             INNER JOIN documents d ON d.id = rt.document_id
             INNER JOIN user_projects up ON up.project_id = d.project_id
             LEFT JOIN users creator ON creator.id = rt.created_by
             LEFT JOIN review_status rs ON rs.review_thread_id = rt.id
             WHERE d.project_id = :project_id
               AND d.id = :document_id
               AND up.user_id = :user_id
             GROUP BY rt.id, rt.title, rt.created_by, creator.fullname, rt.created_at
             ORDER BY rt.created_at DESC'
        );

        $threadStatement->execute([
            'project_id' => $projectId,
            'document_id' => $documentId,
            'user_id' => $userId,
        ]);

        $threads = $threadStatement->fetchAll(\PDO::FETCH_ASSOC);
        if ($threads === []) {
            return [];
        }

        $openVersionsStatement = $pdo->prepare(
            'SELECT rs.review_thread_id,
                    dv.version_number
             FROM review_status rs
             INNER JOIN document_versions dv ON dv.id = rs.document_version_id
             INNER JOIN review_threads rt ON rt.id = rs.review_thread_id
             INNER JOIN documents d ON d.id = rt.document_id
             INNER JOIN user_projects up ON up.project_id = d.project_id
             WHERE d.project_id = :project_id
               AND d.id = :document_id
               AND up.user_id = :user_id
               AND rs.status = \'open\'
             ORDER BY dv.version_number DESC'
        );

        $openVersionsStatement->execute([
            'project_id' => $projectId,
            'document_id' => $documentId,
            'user_id' => $userId,
        ]);

        $openVersionsRows = $openVersionsStatement->fetchAll(\PDO::FETCH_ASSOC);
        $openVersionsByThreadId = [];
        foreach ($openVersionsRows as $row) {
            $threadId = (int) $row['review_thread_id'];
            $versionNumber = (int) $row['version_number'];

            if (!isset($openVersionsByThreadId[$threadId])) {
                $openVersionsByThreadId[$threadId] = [];
            }

            $openVersionsByThreadId[$threadId][] = $versionNumber;
        }

        $normalized = [];
        foreach ($threads as $thread) {
            $threadId = (int) $thread['id'];
            $openVersions = $openVersionsByThreadId[$threadId] ?? [];

            $normalized[] = [
                'id' => $threadId,
                'title' => (string) $thread['title'],
                'created_by' => (int) $thread['created_by'],
                'created_by_name' => $thread['created_by_name'] !== null ? (string) $thread['created_by_name'] : null,
                'created_at' => (string) $thread['created_at'],
                'status' => ((int) $thread['open_count']) > 0 ? 'open' : 'resolved',
                'open_version_numbers' => $openVersions,
            ];
        }

        return $normalized;
    }

    /**
     * @return array<int, array{id:int,review_thread_id:int,document_version_id:int,version_number:int,reviewer_id:int,reviewer_name:string|null,page_number:int,comment:string,created_at:string}>
     */
    public function getReviewCommentsForDocumentForUser(int $projectId, int $documentId, int $userId): array
    {
        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'SELECT rc.id,
                    rc.review_thread_id,
                    rc.document_version_id,
                    dv.version_number,
                    rc.reviewer_id,
                    reviewer.fullname AS reviewer_name,
                    rc.page_number,
                    rc.comment,
                    rc.created_at
             FROM review_comments rc
             INNER JOIN review_threads rt ON rt.id = rc.review_thread_id
             INNER JOIN documents d ON d.id = rt.document_id
             INNER JOIN document_versions dv ON dv.id = rc.document_version_id
             INNER JOIN user_projects up ON up.project_id = d.project_id
             LEFT JOIN users reviewer ON reviewer.id = rc.reviewer_id
             WHERE d.project_id = :project_id
               AND d.id = :document_id
               AND up.user_id = :user_id
             ORDER BY rc.created_at ASC'
        );

        $statement->execute([
            'project_id' => $projectId,
            'document_id' => $documentId,
            'user_id' => $userId,
        ]);

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /** @return array<int, string> */
    public function getThreadStatusMapForVersionForUser(int $projectId, int $documentId, int $userId, int $versionId): array
    {
        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'SELECT rt.id AS review_thread_id,
                    COALESCE(rs.status, \'open\') AS status
             FROM review_threads rt
             INNER JOIN documents d ON d.id = rt.document_id
             INNER JOIN user_projects up ON up.project_id = d.project_id
             LEFT JOIN review_status rs
                    ON rs.review_thread_id = rt.id
                   AND rs.document_version_id = :version_id
             WHERE d.project_id = :project_id
               AND d.id = :document_id
               AND up.user_id = :user_id'
        );

        $statement->execute([
            'project_id' => $projectId,
            'document_id' => $documentId,
            'user_id' => $userId,
            'version_id' => $versionId,
        ]);

        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['review_thread_id']] = (string) $row['status'];
        }

        return $map;
    }

    /** @return array{id:int,document_id:int,created_by:int,title:string}|null */
    public function getReviewThreadForUser(int $projectId, int $documentId, int $threadId, int $userId): ?array
    {
        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'SELECT rt.id,
                    rt.document_id,
                    rt.created_by,
                    rt.title
             FROM review_threads rt
             INNER JOIN documents d ON d.id = rt.document_id
             INNER JOIN user_projects up ON up.project_id = d.project_id
             WHERE d.project_id = :project_id
               AND d.id = :document_id
               AND rt.id = :thread_id
               AND up.user_id = :user_id
             LIMIT 1'
        );

        $statement->execute([
            'project_id' => $projectId,
            'document_id' => $documentId,
            'thread_id' => $threadId,
            'user_id' => $userId,
        ]);

        $thread = $statement->fetch(\PDO::FETCH_ASSOC);
        return $thread ?: null;
    }

    /** @return array{id:int,version_number:int}|null */
    public function getDocumentVersionByNumberForUser(int $projectId, int $documentId, int $userId, int $versionNumber): ?array
    {
        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'SELECT dv.id, dv.version_number
             FROM document_versions dv
             INNER JOIN documents d ON d.id = dv.document_id
             INNER JOIN user_projects up ON up.project_id = d.project_id
             WHERE d.project_id = :project_id
               AND d.id = :document_id
               AND up.user_id = :user_id
               AND dv.version_number = :version_number
             LIMIT 1'
        );

        $statement->execute([
            'project_id' => $projectId,
            'document_id' => $documentId,
            'user_id' => $userId,
            'version_number' => $versionNumber,
        ]);

        $version = $statement->fetch(\PDO::FETCH_ASSOC);
        return $version ?: null;
    }

    public function createReviewThreadWithInitialComment(
        int $threadId,
        int $commentId,
        int $documentId,
        int $createdBy,
        string $title,
        string $comment,
        int $pageNumber,
        int $versionId
    ): void {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $threadStatement = $pdo->prepare(
                'INSERT INTO review_threads (id, document_id, created_by, title)
                 VALUES (:id, :document_id, :created_by, :title)'
            );

            $threadStatement->execute([
                'id' => $threadId,
                'document_id' => $documentId,
                'created_by' => $createdBy,
                'title' => $title,
            ]);

            $statusStatement = $pdo->prepare(
                'INSERT INTO review_status (review_thread_id, document_version_id, status)
                 VALUES (:review_thread_id, :document_version_id, :status)'
            );

            $statusStatement->execute([
                'review_thread_id' => $threadId,
                'document_version_id' => $versionId,
                'status' => 'open',
            ]);

            $commentStatement = $pdo->prepare(
                'INSERT INTO review_comments (
                    id,
                    review_thread_id,
                    document_version_id,
                    reviewer_id,
                    page_number,
                    comment,
                    created_at
                 ) VALUES (
                    :id,
                    :review_thread_id,
                    :document_version_id,
                    :reviewer_id,
                    :page_number,
                    :comment,
                    CURRENT_TIMESTAMP
                 )'
            );

            $commentStatement->execute([
                'id' => $commentId,
                'review_thread_id' => $threadId,
                'document_version_id' => $versionId,
                'reviewer_id' => $createdBy,
                'page_number' => $pageNumber,
                'comment' => $comment,
            ]);

            $pdo->commit();
        } catch (Throwable $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }

    public function createReviewComment(
        int $commentId,
        int $threadId,
        int $versionId,
        int $reviewerId,
        int $pageNumber,
        string $comment
    ): void {
        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'INSERT INTO review_comments (
                id,
                review_thread_id,
                document_version_id,
                reviewer_id,
                page_number,
                comment,
                created_at
             ) VALUES (
                :id,
                :review_thread_id,
                :document_version_id,
                :reviewer_id,
                :page_number,
                :comment,
                CURRENT_TIMESTAMP
             )'
        );

        $statement->execute([
            'id' => $commentId,
            'review_thread_id' => $threadId,
            'document_version_id' => $versionId,
            'reviewer_id' => $reviewerId,
            'page_number' => $pageNumber,
            'comment' => $comment,
        ]);
    }

    public function upsertReviewStatusOpen(int $threadId, int $versionId): void
    {
        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'INSERT INTO review_status (
                review_thread_id,
                document_version_id,
                status,
                resolved_by,
                resolved_at
             ) VALUES (
                :review_thread_id,
                :document_version_id,
                :status,
                NULL,
                NULL
             )
             ON DUPLICATE KEY UPDATE
                status = VALUES(status),
                resolved_by = NULL,
                resolved_at = NULL'
        );

        $statement->execute([
            'review_thread_id' => $threadId,
            'document_version_id' => $versionId,
            'status' => 'open',
        ]);
    }

    public function resolveReviewThreadForVersion(int $threadId, int $versionId, int $resolvedBy): void
    {
        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'INSERT INTO review_status (
                review_thread_id,
                document_version_id,
                status,
                resolved_by,
                resolved_at
             ) VALUES (
                :review_thread_id,
                :document_version_id,
                :status,
                :resolved_by,
                CURRENT_TIMESTAMP
             )
             ON DUPLICATE KEY UPDATE
                status = VALUES(status),
                resolved_by = VALUES(resolved_by),
                resolved_at = CURRENT_TIMESTAMP'
        );

        $statement->execute([
            'review_thread_id' => $threadId,
            'document_version_id' => $versionId,
            'status' => 'resolved',
            'resolved_by' => $resolvedBy,
        ]);
    }
}

