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
}

