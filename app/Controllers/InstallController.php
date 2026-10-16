<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Installer;
use App\Core\Request;

final class InstallController extends Controller
{
    public function form(): void
    {
        $this->render('install/form', [
            'wide' => true,
            'title' => 'Instalação',
            'v' => [
                'db_host' => 'localhost', 'db_port' => '3306', 'db_name' => '', 'db_user' => '', 'db_pass' => '',
                'app_url' => Config::appUrl(), 'admin_email' => 'admin@drogacare.com.br', 'smtp_url' => '', 'mail_from' => 'Drogacare <nao-responda@drogacare.com.br>',
            ],
            'errors' => [],
            'checks' => $this->checks(),
        ], 'layouts/auth');
    }

    public function run(): void
    {
        $v = [];
        foreach (['db_host', 'db_port', 'db_name', 'db_user', 'app_url', 'admin_email', 'smtp_url', 'mail_from'] as $k) {
            $v[$k] = Request::str($k);
        }
        $v['db_pass'] = Request::raw('db_pass');

        $errors = [];
        if ($v['db_host'] === '') $errors['db_host'] = 'Informe o servidor do banco.';
        if (!ctype_digit($v['db_port'])) $errors['db_port'] = 'Porta inválida.';
        if ($v['db_name'] === '') $errors['db_name'] = 'Informe o nome do banco.';
        if ($v['db_user'] === '') $errors['db_user'] = 'Informe o usuário do banco.';
        if (!filter_var($v['app_url'], FILTER_VALIDATE_URL)) $errors['app_url'] = 'Informe a URL completa, ex.: https://www.seudominio.com.br';
        if (!filter_var($v['admin_email'], FILTER_VALIDATE_EMAIL)) $errors['admin_email'] = 'E-mail inválido.';

        $error = null;
        $log = [];
        if (!$errors) {
            try {
                $log = Installer::run([
                    'db_host' => $v['db_host'], 'db_port' => (int) $v['db_port'], 'db_name' => $v['db_name'],
                    'db_user' => $v['db_user'], 'db_pass' => $v['db_pass'], 'app_url' => $v['app_url'],
                    'admin_email' => $v['admin_email'], 'smtp_url' => $v['smtp_url'], 'mail_from' => $v['mail_from'],
                ]);
                $this->render('install/done', ['wide' => true, 'title' => 'Instalação concluída', 'log' => $log, 'email' => strtolower($v['admin_email'])], 'layouts/auth');
                return;
            } catch (\Throwable $e) {
                $error = 'Não foi possível concluir: ' . $e->getMessage();
            }
        }
        $this->render('install/form', ['wide' => true, 'title' => 'Instalação', 'v' => $v, 'errors' => $errors, 'error' => $error, 'checks' => $this->checks()], 'layouts/auth', 422);
    }

    private function checks(): array
    {
        return [
            'PHP 8.1 ou superior (atual: ' . PHP_VERSION . ')' => PHP_VERSION_ID >= 80100,
            'Extensão pdo_mysql' => extension_loaded('pdo_mysql'),
            'Extensão mbstring' => extension_loaded('mbstring'),
            'Extensão openssl (SMTP seguro)' => extension_loaded('openssl'),
            'Pasta config/ gravável' => is_writable(ROOT . '/config'),
            'Pasta storage/ gravável' => is_writable(ROOT . '/storage'),
            'Pasta storage/sessions gravável' => is_writable(ROOT . '/storage/sessions'),
        ];
    }
}
