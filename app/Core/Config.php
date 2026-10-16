<?php
declare(strict_types=1);

namespace App\Core;

final class Config
{
    private static array $data = [];

    public static function load(): void
    {
        $file = ROOT . '/config/config.php';
        self::$data = is_file($file) ? (array) require $file : [];
    }

    public static function installed(): bool
    {
        return self::$data !== [] && is_file(ROOT . '/storage/installed.lock');
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = self::$data;
        foreach (explode('.', $key) as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return $default;
            }
            $value = $value[$part];
        }
        return $value;
    }

    /** Caminho base da instalação (ex.: "" na raiz do domínio ou "/drogacare" em subpasta). */
    public static function basePath(): string
    {
        static $base = null;
        if ($base === null) {
            $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
            $dir = preg_replace('#/public$#', '', $dir) ?? '';
            $base = rtrim($dir === '/' ? '' : $dir, '/');
        }
        return $base;
    }

    public static function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'
            || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
    }

    public static function appUrl(): string
    {
        $configured = (string) self::get('app_url', '');
        if ($configured !== '') {
            return rtrim($configured, '/');
        }
        $scheme = self::isHttps() ? 'https' : 'http';
        return $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . self::basePath();
    }
}
