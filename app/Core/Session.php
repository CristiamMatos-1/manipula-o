<?php
declare(strict_types=1);

namespace App\Core;

final class Session
{
    public const LIFETIME = 28800; // 8 horas

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        $dir = ROOT . '/storage/sessions';
        if (is_dir($dir) && is_writable($dir)) {
            session_save_path($dir);
        }
        session_name('dc_session');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.gc_maxlifetime', (string) self::LIFETIME);
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => Config::isHttps(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();

        // Expira sessões muito antigas ou inativas por mais de 2 horas.
        $now = time();
        if (isset($_SESSION['started_at']) && ($now - $_SESSION['started_at'] > self::LIFETIME || $now - ($_SESSION['last_seen'] ?? $now) > 7200)) {
            self::destroy();
            session_start();
        }
        $_SESSION['started_at'] ??= $now;
        $_SESSION['last_seen'] = $now;
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        setcookie('dc_session', '', ['expires' => time() - 3600, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
    }

    public static function flash(string $type, string $message): void
    {
        $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
    }

    public static function pullFlash(): array
    {
        $f = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $f;
    }
}
