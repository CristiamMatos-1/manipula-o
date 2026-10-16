<?php
declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = [], ?string $layout = null): void
    {
        echo self::capture($view, $data, $layout);
    }

    public static function capture(string $view, array $data = [], ?string $layout = null): string
    {
        $content = self::include($view, $data);
        if ($layout === null) {
            return $content;
        }
        return self::include($layout, $data + ['_content' => $content]);
    }

    private static function include(string $view, array $data): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require APP_PATH . '/Views/' . $view . '.php';
        return (string) ob_get_clean();
    }
}
