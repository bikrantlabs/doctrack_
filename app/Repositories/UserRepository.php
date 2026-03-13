<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Exceptions\DuplicateEmailException;
use PDOException;

final class UserRepository
{
    /** @return array{id:int,email:string,fullname:string,password:string}|null */
    public function findByEmail(string $email): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, email, fullname, password FROM users WHERE email = :email LIMIT 1'
        );
        $statement->execute(['email' => $email]);

        $user = $statement->fetch();
        if (!$user) {
            return null;
        }

        return [
            'id' => (int) $user['id'],
            'email' => (string) $user['email'],
            'fullname' => (string) $user['fullname'],
            'password' => (string) $user['password'],
        ];
    }

    public function create(string $email, string $fullname, string $hashedPassword): int
    {
        $pdo = Database::connection();

        try {
            $statement = $pdo->prepare(
                'INSERT INTO users (email, fullname, password) VALUES (:email, :fullname, :password)'
            );
            $statement->execute([
                'email' => $email,
                'fullname' => $fullname,
                'password' => $hashedPassword,
            ]);
        } catch (PDOException $exception) {
            if ((string) $exception->getCode() === '23000') {
                throw new DuplicateEmailException('Email already exists.', 0, $exception);
            }

            throw $exception;
        }

        return (int) $pdo->lastInsertId();
    }

    /** @param array<int, int> $userIds @return array<int, int> */
    public function findByIds(array $userIds): array
    {
        $userIds = array_values(array_unique(array_map(static fn (int $id): int => (int) $id, $userIds)));
        if ($userIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $statement = Database::connection()->prepare("SELECT id FROM users WHERE id IN ({$placeholders})");
        $statement->execute($userIds);

        $rows = $statement->fetchAll();

        return array_map(static fn (array $row): int => (int) $row['id'], $rows ?: []);
    }

    /** @return array<int, array{id:int,fullname:string,email:string}> */
    public function searchByNameOrEmail(string $query, int $excludeUserId, int $limit = 10): array
    {
        $trimmedQuery = trim($query);
        if ($trimmedQuery === '') {
            return [];
        }

        $statement = Database::connection()->prepare(
            'SELECT id, fullname, email
             FROM users
             WHERE id != :excludeUserId
               AND (fullname LIKE :query OR email LIKE :query)
             ORDER BY fullname ASC
             LIMIT :limit'
        );
        $statement->bindValue(':excludeUserId', $excludeUserId, \PDO::PARAM_INT);
        $statement->bindValue(':query', '%' . $trimmedQuery . '%');
        $statement->bindValue(':limit', max(1, min(25, $limit)), \PDO::PARAM_INT);
        $statement->execute();

        $rows = $statement->fetchAll();
        if (!$rows) {
            return [];
        }

        return array_map(static fn (array $row): array => [
            'id' => (int) $row['id'],
            'fullname' => (string) $row['fullname'],
            'email' => (string) $row['email'],
        ], $rows);
    }
}

