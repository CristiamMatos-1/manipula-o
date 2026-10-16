<?php
declare(strict_types=1);

define('ROOT', dirname(__DIR__));
define('APP_PATH', __DIR__);

spl_autoload_register(static function (string $class): void {
    if (!str_starts_with($class, 'App\\')) {
        return;
    }
    $file = APP_PATH . '/' . str_replace('\\', '/', substr($class, 4)) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

require APP_PATH . '/Core/helpers.php';

use App\Core\Config;

Config::load();
date_default_timezone_set('America/Sao_Paulo');
mb_internal_encoding('UTF-8');

ini_set('log_errors', '1');
ini_set('error_log', ROOT . '/storage/logs/php-error.log');
ini_set('display_errors', Config::get('debug') ? '1' : '0');
error_reporting(E_ALL);
