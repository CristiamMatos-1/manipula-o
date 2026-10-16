<?php
declare(strict_types=1);

// Servidor embutido do PHP (php -S): entrega arquivos estáticos diretamente.
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($file) && basename($file) !== 'index.php') {
        return false;
    }
}

require __DIR__ . '/../app/bootstrap.php';

App\Core\App::run();
