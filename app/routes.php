<?php
declare(strict_types=1);

use App\Controllers\Admin\{AuditController, AuthController, CompanyController, DashboardController, PagesController, PostsController, SiteSettingsController, UsersController};
use App\Controllers\{InstallController, SiteController};
use App\Core\Router;

return static function (Router $r, bool $installed): void {
    if (!$installed) {
        foreach (['/install', '/install.php'] as $path) {
            $r->get($path, [InstallController::class, 'form']);
            $r->post($path, [InstallController::class, 'run']);
        }
        return;
    }

    // Site público
    $r->get('/', [SiteController::class, 'home']);
    $r->get('/feed', [SiteController::class, 'feed']);
    $r->get('/blog', [SiteController::class, 'blog']);
    $r->get('/blog/{slug}', [SiteController::class, 'post']);
    $r->get('/p/{slug}', [SiteController::class, 'page']);
    $r->get('/sitemap.xml', [SiteController::class, 'sitemap']);
    $r->get('/robots.txt', [SiteController::class, 'robots']);

    // Autenticação
    $r->get('/admin/login', [AuthController::class, 'loginForm']);
    $r->post('/admin/login', [AuthController::class, 'login']);
    $r->post('/admin/logout', [AuthController::class, 'logout']);
    $r->get('/admin/esqueci-senha', [AuthController::class, 'forgotForm']);
    $r->post('/admin/esqueci-senha', [AuthController::class, 'forgot']);
    $r->get('/admin/redefinir-senha', [AuthController::class, 'resetForm']);
    $r->post('/admin/redefinir-senha', [AuthController::class, 'reset']);
    $r->get('/admin/alterar-senha', [AuthController::class, 'changeForm']);
    $r->post('/admin/alterar-senha', [AuthController::class, 'change']);
    $r->get('/admin/acesso-negado', [AuthController::class, 'forbidden']);

    // Painel
    $r->get('/admin', [DashboardController::class, 'index']);

    foreach (['/admin/posts' => PostsController::class, '/admin/paginas' => PagesController::class] as $base => $ctl) {
        $r->get($base, [$ctl, 'index']);
        $r->get("$base/novo", [$ctl, 'create']);
        $r->post("$base/novo", [$ctl, 'store']);
        $r->get("$base/{id}", [$ctl, 'edit']);
        $r->post("$base/{id}", [$ctl, 'update']);
        $r->post("$base/{id}/excluir", [$ctl, 'destroy']);
    }

    $r->get('/admin/empresa', [CompanyController::class, 'edit']);
    $r->post('/admin/empresa', [CompanyController::class, 'update']);

    $r->get('/admin/pagina-inicial', [SiteSettingsController::class, 'edit']);
    $r->post('/admin/pagina-inicial', [SiteSettingsController::class, 'update']);

    $r->get('/admin/usuarios', [UsersController::class, 'index']);
    $r->get('/admin/usuarios/novo', [UsersController::class, 'create']);
    $r->post('/admin/usuarios/novo', [UsersController::class, 'store']);
    $r->get('/admin/usuarios/{id}', [UsersController::class, 'edit']);
    $r->post('/admin/usuarios/{id}', [UsersController::class, 'update']);
    $r->post('/admin/usuarios/{id}/excluir', [UsersController::class, 'destroy']);

    $r->get('/admin/auditoria', [AuditController::class, 'index']);
};
