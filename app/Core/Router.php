<?php
declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<int, array{0:string,1:string,2:array}> */
    private array $routes = [];

    public function add(string $method, string $pattern, array $handler): void
    {
        $regex = preg_replace_callback('#\{(\w+)\}#', static fn ($m) => $m[1] === 'id' ? '(?P<id>\d+)' : '(?P<' . $m[1] . '>[^/]+)', $pattern);
        $this->routes[] = [$method, '#^' . $regex . '$#', $handler];
    }

    public function get(string $p, array $h): void { $this->add('GET', $p, $h); }
    public function post(string $p, array $h): void { $this->add('POST', $p, $h); }

    public function dispatch(string $method, string $path): void
    {
        if ($method === 'HEAD') {
            $method = 'GET';
        }
        foreach ($this->routes as [$m, $regex, $handler]) {
            if ($m !== $method || !preg_match($regex, $path, $matches)) {
                continue;
            }
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

            if (str_starts_with($path, '/admin') || str_starts_with($path, '/install')) {
                Session::start();
                header('Cache-Control: no-store');
                header('X-Robots-Tag: noindex, nofollow');
                if ($method === 'POST' && !Csrf::valid($_POST['_csrf'] ?? null)) {
                    http_response_code(419);
                    View::render('auth/expired', ['title' => 'Sessão expirada'], 'layouts/auth');
                    return;
                }
            }

            [$class, $action] = $handler;
            (new $class())->$action($params);
            return;
        }
        $this->notFound();
    }

    public function notFound(): void
    {
        http_response_code(404);
        if (str_starts_with(Request::path(), '/admin')) {
            View::render('auth/not_found', ['title' => 'Página não encontrada'], 'layouts/auth');
            return;
        }
        $data = ['title' => 'Página não encontrada', 'meta' => ['title' => 'Página não encontrada']];
        try {
            $data['company'] = \App\Models\Company::get();
        } catch (\Throwable) {
        }
        View::render('site/404', $data, 'layouts/site');
    }
}
