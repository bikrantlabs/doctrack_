<?php

declare(strict_types=1);

namespace App\Core;

final class Auth
{
    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    /** @return array{id:int,email:string,fullname:string}|null */
    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    /** @param array{id:int,email:string,fullname:string} $user */
    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user'] = $user;
    }

    public static function logout(): void
    {
        unset($_SESSION['user']);
        session_regenerate_id(true);
    }
}

