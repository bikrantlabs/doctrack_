<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    /** @param array<string, mixed> $params */
    public static function render(string $view, array $params = [], string $title = ''): void
    {
        $viewPath = BASE_PATH . '/views/' . $view . '.php';
        $layoutPath = BASE_PATH . '/views/layout.php';

        if (!is_file($viewPath)) {
            throw new \RuntimeException("View not found: {$view}");
        }

        extract($params, EXTR_SKIP);

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        require $layoutPath;
    }
}

