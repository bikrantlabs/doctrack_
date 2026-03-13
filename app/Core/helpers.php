<?php

declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = '/'): string
{
    $baseUrl = defined('BASE_URL') ? BASE_URL : '';
    $path = '/' . ltrim($path, '/');

    if ($baseUrl === '' || $baseUrl === '/') {
        return $path;
    }

    return rtrim($baseUrl, '/') . $path;
}

function current_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $path = is_string($path) ? $path : '/';

    $baseUrl = defined('BASE_URL') ? (string) BASE_URL : '';
    if ($baseUrl !== '' && str_starts_with($path, $baseUrl)) {
        $path = substr($path, strlen($baseUrl));
    }

    $path = '/' . trim($path, '/');

    return $path === '//' ? '/' : $path;
}

function old(string $key, string $default = ''): string
{
    return $_SESSION['_old'][$key] ?? $default;
}

function flash(string $key): ?string
{
    if (!isset($_SESSION['_flash'][$key])) {
        return null;
    }

    $message = $_SESSION['_flash'][$key];
    unset($_SESSION['_flash'][$key]);

    return $message;
}

/** @return array<int, array{type:string,message:string}> */
function take_flash_messages(): array
{
    if (!isset($_SESSION['_flash']) || !is_array($_SESSION['_flash'])) {
        return [];
    }

    $messages = [];

    foreach ($_SESSION['_flash'] as $type => $message) {
        if (!is_string($type) || !is_string($message) || $message === '') {
            continue;
        }

        $messages[] = ['type' => $type, 'message' => $message];
    }

    unset($_SESSION['_flash']);

    return $messages;
}

