<?php
declare(strict_types=1);

namespace App\Core;

final class Request
{
    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public static function path(): string
    {
        if (defined('FORCE_PATH')) {
            return FORCE_PATH;
        }
        $path = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $base = Config::basePath();
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }
        $path = preg_replace('#/index\.php$#', '', $path) ?? $path;
        $path = rawurldecode($path);
        $path = '/' . trim($path, '/');
        return $path === '' ? '/' : $path;
    }

    public static function ip(): ?string
    {
        // Atrás de proxy/CDN confie em X-Forwarded-For apenas se o proxy for seu.
        $fwd = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
        $ip = $fwd !== '' ? trim(explode(',', $fwd)[0]) : ($_SERVER['REMOTE_ADDR'] ?? '');
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : null;
    }

    public static function str(string $key): string
    {
        $v = $_POST[$key] ?? '';
        return is_string($v) ? trim($v) : '';
    }

    public static function raw(string $key): string
    {
        $v = $_POST[$key] ?? '';
        return is_string($v) ? $v : '';
    }

    public static function query(string $key): string
    {
        $v = $_GET[$key] ?? '';
        return is_string($v) ? trim($v) : '';
    }
}
