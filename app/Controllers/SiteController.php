<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Company;
use App\Models\ContentRepo;

final class SiteController extends Controller
{
    public function home(): void
    {
        $this->render('site/home', [
            'company' => Company::get(),
            'posts' => ContentRepo::posts()->published(3),
            'meta' => [],
        ], 'layouts/site');
    }

    public function blog(): void
    {
        $this->render('site/blog', [
            'company' => Company::get(),
            'posts' => ContentRepo::posts()->published(),
            'meta' => ['title' => 'Artigos', 'description' => 'Dicas e informações sobre saúde, bem-estar e manipulação de medicamentos.', 'canonical' => '/blog'],
        ], 'layouts/site');
    }

    public function post(array $p): void
    {
        $this->article(ContentRepo::posts(), '/blog', $p['slug']);
    }

    public function page(array $p): void
    {
        $this->article(ContentRepo::pages(), '/p', $p['slug']);
    }

    private function article(ContentRepo $repo, string $base, string $slug): void
    {
        $item = $repo->findPublishedBySlug($slug);
        if (!$item) {
            (new \App\Core\Router())->notFound();
            return;
        }
        $image = safe_image_url($item['og_image_url'] ?? null) ?? safe_image_url($item['cover_url'] ?? null);
        $this->render('site/article', [
            'company' => Company::get(),
            'item' => $item,
            'basePath' => $base,
            'isPost' => $base === '/blog',
            'meta' => [
                'title' => $item['seo_title'] ?: $item['title'],
                'description' => $item['seo_description'] ?: $item['excerpt'],
                'keywords' => $item['seo_keywords'],
                'canonical' => $base . '/' . $item['slug'],
                'image' => $image,
                'type' => 'article',
            ],
        ], 'layouts/site');
    }

    public function sitemap(): void
    {
        header('Content-Type: application/xml; charset=utf-8');
        $urls = [[abs_url('/'), null], [abs_url('/blog'), null]];
        foreach (['/blog' => ContentRepo::posts(), '/p' => ContentRepo::pages()] as $base => $repo) {
            foreach (\App\Core\Database::all("SELECT slug, updated_at FROM {$repo->table} WHERE status = 'PUBLISHED'") as $row) {
                $urls[] = [abs_url($base . '/' . $row['slug']), date('c', strtotime($row['updated_at']))];
            }
        }
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as [$loc, $mod]) {
            echo '  <url><loc>' . e($loc) . '</loc>' . ($mod ? '<lastmod>' . $mod . '</lastmod>' : '') . "</url>\n";
        }
        echo '</urlset>';
    }

    public function robots(): void
    {
        header('Content-Type: text/plain; charset=utf-8');
        echo "User-agent: *\nAllow: /\nDisallow: /admin\n\nSitemap: " . abs_url('/sitemap.xml') . "\n";
    }
}
