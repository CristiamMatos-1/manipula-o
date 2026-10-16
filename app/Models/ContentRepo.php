<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/** Repositório compartilhado por artigos (posts) e páginas (pages). A tabela vem de uma lista fixa. */
final class ContentRepo
{
    private function __construct(public readonly string $table, public readonly bool $hasCover)
    {
    }

    public static function posts(): self { return new self('posts', true); }
    public static function pages(): self { return new self('pages', false); }

    public static function for(string $kind): self
    {
        return $kind === 'post' ? self::posts() : self::pages();
    }

    public function list(): array
    {
        return Database::all("SELECT c.id, c.title, c.slug, c.status, c.updated_at, c.published_at, u.name AS author_name
            FROM {$this->table} c LEFT JOIN users u ON u.id = c.author_id ORDER BY c.updated_at DESC");
    }

    public function published(?int $limit = null): array
    {
        $cover = $this->hasCover ? 'cover_url' : 'NULL AS cover_url';
        $sql = "SELECT slug, title, excerpt, $cover, published_at FROM {$this->table} WHERE status = 'PUBLISHED' ORDER BY published_at DESC";
        if ($limit) {
            $sql .= ' LIMIT ' . (int) $limit;
        }
        return Database::all($sql);
    }

    public function find(int $id): ?array
    {
        return Database::one("SELECT * FROM {$this->table} WHERE id = ?", [$id]);
    }

    public function findPublishedBySlug(string $slug): ?array
    {
        return Database::one("SELECT * FROM {$this->table} WHERE slug = ? AND status = 'PUBLISHED'", [$slug]);
    }

    public function slugTaken(string $slug, ?int $exceptId): bool
    {
        $row = Database::one("SELECT id FROM {$this->table} WHERE slug = ?", [$slug]);
        return $row !== null && (int) $row['id'] !== $exceptId;
    }

    public function count(?string $status = null): int
    {
        return (int) ($status
            ? Database::value("SELECT COUNT(*) FROM {$this->table} WHERE status = ?", [$status])
            : Database::value("SELECT COUNT(*) FROM {$this->table}"));
    }

    public function save(?int $id, array $d): int
    {
        $cols = ['title', 'slug', 'excerpt', 'content', 'seo_title', 'seo_description', 'seo_keywords', 'og_image_url', 'status', 'published_at'];
        if ($this->hasCover) {
            $cols[] = 'cover_url';
        }
        if ($id) {
            $sets = implode(', ', array_map(static fn ($c) => "`$c` = ?", $cols));
            Database::run("UPDATE {$this->table} SET $sets, updated_at = NOW() WHERE id = ?", [...array_map(static fn ($c) => $d[$c] ?? null, $cols), $id]);
            return $id;
        }
        $cols[] = 'author_id';
        $names = implode(', ', array_map(static fn ($c) => "`$c`", $cols));
        $marks = implode(', ', array_fill(0, count($cols), '?'));
        return Database::insert("INSERT INTO {$this->table} ($names, created_at, updated_at) VALUES ($marks, NOW(), NOW())", array_map(static fn ($c) => $d[$c] ?? null, $cols));
    }

    public function setStatus(int $id, string $status): void
    {
        Database::run("UPDATE {$this->table} SET status = ?, updated_at = NOW() WHERE id = ?", [$status, $id]);
    }

    public function delete(int $id): void
    {
        Database::run("DELETE FROM {$this->table} WHERE id = ?", [$id]);
    }
}
