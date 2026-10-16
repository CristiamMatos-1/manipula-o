<?php
declare(strict_types=1);

namespace App\Core;

final class App
{
    public static function run(): void
    {
        self::securityHeaders();
        set_exception_handler([self::class, 'handleException']);

        $installed = Config::installed();
        if (!$installed && !in_array(Request::path(), ['/install', '/install.php'], true)) {
            redirect('/install.php');
        }

        $router = new Router();
        (require APP_PATH . '/routes.php')($router, $installed);
        $router->dispatch(Request::method(), Request::path());
    }

    private static function securityHeaders(): void
    {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
        header('Content-Security-Policy: ' . implode('; ', [
            "default-src 'self'",
            "script-src 'self'",
            "style-src 'self'",
            "img-src 'self' data: https: http:",
            "media-src 'self' https: http:",
            "font-src 'self'",
            "frame-src https://www.youtube-nocookie.com https://www.google.com",
            "connect-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
        ]));
        if (Config::isHttps()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
        header_remove('X-Powered-By');
    }

    public static function handleException(\Throwable $e): void
    {
        error_log((string) $e);
        if (!headers_sent()) {
            http_response_code(500);
        }
        if (Config::get('debug')) {
            echo '<pre style="padding:1rem;white-space:pre-wrap">' . e((string) $e) . '</pre>';
            return;
        }
        echo '<!doctype html><html lang="pt-BR"><meta charset="utf-8"><title>Erro</title><body style="font-family:Arial,sans-serif;text-align:center;padding:4rem 1rem"><h1>Algo deu errado</h1><p>Tente novamente em instantes. Se o problema persistir, contate o administrador.</p></body></html>';
    }
}
