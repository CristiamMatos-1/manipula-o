<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\{Audit, Auth, Rbac, Request, Router};
use App\Models\ContentRepo;

abstract class ContentController extends AdminController
{
    protected string $kind;       // post | page
    protected string $basePath;   // /admin/posts
    protected string $publicPath; // /blog
    protected string $singular;   // artigo
    protected string $plural;     // Artigos
    protected string $entity;     // Post

    protected function repo(): ContentRepo
    {
        return ContentRepo::for($this->kind);
    }

    public function index(): void
    {
        $user = Auth::requirePermission('content:view');
        $this->render('admin/content_list', ['title' => $this->plural, 'user' => $user, 'items' => $this->repo()->list(), 'c' => $this->ctx()]);
    }

    public function create(): void
    {
        $user = Auth::requirePermission('content:write');
        $this->render('admin/content_form', ['title' => 'Novo ' . $this->singular, 'user' => $user, 'item' => null, 'v' => [], 'errors' => [], 'c' => $this->ctx()]);
    }

    public function store(): void
    {
        $this->save(null);
    }

    public function edit(array $p): void
    {
        $user = Auth::requirePermission('content:view');
        $item = $this->repo()->find((int) $p['id']);
        if (!$item) {
            (new Router())->notFound();
            return;
        }
        $this->render('admin/content_form', ['title' => $item['title'], 'user' => $user, 'item' => $item, 'v' => [], 'errors' => [], 'c' => $this->ctx()]);
    }

    public function update(array $p): void
    {
        $this->save((int) $p['id']);
    }

    private function ctx(): array
    {
        return ['kind' => $this->kind, 'base' => $this->basePath, 'public' => $this->publicPath, 'singular' => $this->singular, 'plural' => $this->plural];
    }

    /**
     * "intent" define o ciclo de vida:
     *  - draft:   salva sem publicar (mantém/volta para RASCUNHO)
     *  - publish: salva e publica (registra published_at na 1ª publicação)
     */
    private function save(?int $id): void
    {
        $user = Auth::requirePermission('content:write');
        $intent = Request::str('intent') === 'publish' ? 'publish' : 'draft';
        if ($intent === 'publish') {
            Auth::requirePermission('content:publish');
        }
        $repo = $this->repo();
        $existing = $id ? $repo->find($id) : null;
        if ($id && !$existing) {
            (new Router())->notFound();
            return;
        }

        $v = [
            'title' => Request::str('title'),
            'slug' => Request::str('slug'),
            'excerpt' => Request::str('excerpt'),
            'content' => trim(Request::raw('content')),
            'seo_title' => Request::str('seo_title'),
            'seo_description' => Request::str('seo_description'),
            'seo_keywords' => Request::str('seo_keywords'),
            'og_image_url' => Request::str('og_image_url'),
        ];

        $errors = [];
        $len = static fn (string $s) => mb_strlen($s);
        if ($len($v['title']) < 3) $errors['title'] = 'O título precisa de ao menos 3 caracteres.';
        if ($len($v['title']) > 150) $errors['title'] = 'O título pode ter no máximo 150 caracteres.';
        if ($v['content'] === '') $errors['content'] = 'O conteúdo é obrigatório.';
        if ($len($v['content']) > 100000) $errors['content'] = 'Conteúdo longo demais.';
        if ($len($v['excerpt']) > 300) $errors['excerpt'] = 'Máximo de 300 caracteres.';
        if ($len($v['seo_title']) > 70) $errors['seo_title'] = 'Máximo de 70 caracteres.';
        if ($len($v['seo_description']) > 160) $errors['seo_description'] = 'Máximo de 160 caracteres.';
        if ($len($v['seo_keywords']) > 200) $errors['seo_keywords'] = 'Máximo de 200 caracteres.';
        if ($v['og_image_url'] !== '' && !safe_image_url($v['og_image_url'])) $errors['og_image_url'] = 'Use uma URL http(s) ou um caminho que comece com /.';
        $showOnHome = $this->kind === 'page' && (isset($_POST['show_on_home_present'])
            ? Request::str('show_on_home') === '1'
            : (bool) ($existing['show_on_home'] ?? 1));

        $slug = slugify($v['slug'] !== '' ? $v['slug'] : $v['title']);
        if ($slug === '' && !isset($errors['title'])) $errors['slug'] = 'Slug inválido.';
        elseif ($slug !== '' && $repo->slugTaken($slug, $id)) $errors['slug'] = 'Já existe um item com este endereço (slug).';

        $v['show_on_home'] = $showOnHome ? '1' : '0';
        $oldCover = $existing['cover_url'] ?? null;
        $cover = $errors ? $oldCover : $this->imageValue('cover_url', $oldCover, $errors);

        if ($errors) {
            $this->render('admin/content_form', ['title' => $existing['title'] ?? 'Novo ' . $this->singular, 'user' => $user, 'item' => $existing, 'v' => $v, 'errors' => $errors, 'c' => $this->ctx()], 'layouts/admin', 422);
            return;
        }

        $now = date('Y-m-d H:i:s');
        $wasPublished = ($existing['status'] ?? null) === 'PUBLISHED';
        $savedId = $repo->save($id, [
            'title' => $v['title'], 'slug' => $slug,
            'excerpt' => $v['excerpt'] ?: null, 'content' => $v['content'],
            'cover_url' => $cover ?: null,
            'show_on_home' => $showOnHome ? 1 : 0,
            'seo_title' => $v['seo_title'] ?: null, 'seo_description' => $v['seo_description'] ?: null,
            'seo_keywords' => $v['seo_keywords'] ?: null, 'og_image_url' => $v['og_image_url'] ?: null,
            'status' => $intent === 'publish' ? 'PUBLISHED' : 'DRAFT',
            'published_at' => $intent === 'publish' ? ($existing['published_at'] ?? $now) : ($existing['published_at'] ?? null),
            'author_id' => $user['id'],
        ]);

        $this->discardReplacedImage($oldCover, $cover ?: null);

        $E = strtoupper($this->entity);
        $action = $intent === 'publish'
            ? ($wasPublished ? "{$E}_UPDATE_PUBLISHED" : "{$E}_PUBLISH")
            : ($wasPublished ? "{$E}_UNPUBLISH" : ($existing ? "{$E}_UPDATE" : "{$E}_CREATE"));
        Audit::log($user, $action, $this->entity, $savedId, "\"{$v['title']}\" (/{$slug})");

        $msg = $intent === 'publish' ? 'Publicado com sucesso. O conteúdo já está no ar.' : 'Rascunho salvo. O conteúdo NÃO está visível no site.';
        $this->flashRedirect('success', $msg, $this->basePath . '/' . $savedId);
    }

    public function destroy(array $p): void
    {
        $user = Auth::requirePermission('content:delete');
        $repo = $this->repo();
        $item = $repo->find((int) $p['id']);
        if ($item) {
            $repo->delete((int) $item['id']);
            Audit::log($user, strtoupper($this->entity) . '_DELETE', $this->entity, $item['id'], "\"{$item['title']}\" (/{$item['slug']})");
        }
        $this->flashRedirect('success', ucfirst($this->singular) . ' excluído.', $this->basePath);
    }
}
