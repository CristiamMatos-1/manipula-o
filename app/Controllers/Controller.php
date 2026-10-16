<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Core\View;

abstract class Controller
{
    protected function render(string $view, array $data = [], string $layout = 'layouts/admin', int $status = 200): void
    {
        http_response_code($status);
        View::render($view, $data, $layout);
    }

    protected function flashRedirect(string $type, string $message, string $to): never
    {
        Session::flash($type, $message);
        redirect($to);
    }
}
