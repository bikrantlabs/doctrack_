<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    /** @param array<string, mixed> $params */
    protected function render(string $view, array $params = [], string $title = ''): void
    {
        View::render($view, $params, $title);
        unset($_SESSION['_old']);
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }

    protected function flash(string $type, string $message): void
    {
        $_SESSION['_flash'][$type] = $message;
    }

    /** @param array<string, string> $data */
    protected function keepOld(array $data): void
    {
        $_SESSION['_old'] = $data;
    }
}

