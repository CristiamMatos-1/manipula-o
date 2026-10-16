<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Atualiza bancos já instalados quando o código ganha novas colunas.
 * É idempotente e só roda quando o número em storage/schema.version difere de VERSION.
 */
final class Migrator
{
    public const VERSION = 3;

    public static function file(): string
    {
        return ROOT . '/storage/schema.version';
    }

    public static function markCurrent(): void
    {
        @file_put_contents(self::file(), (string) self::VERSION);
    }

    public static function run(): void
    {
        $current = is_file(self::file()) ? (int) trim((string) @file_get_contents(self::file())) : 0;
        if ($current >= self::VERSION) {
            return;
        }
        try {
            $addedPageCols = false;
            if (!self::hasColumn('pages', 'cover_url')) {
                Database::run('ALTER TABLE pages ADD COLUMN cover_url VARCHAR(500) NULL AFTER content');
            }
            if (!self::hasColumn('pages', 'show_on_home')) {
                Database::run('ALTER TABLE pages ADD COLUMN show_on_home TINYINT(1) NOT NULL DEFAULT 1 AFTER cover_url');
                Database::run("UPDATE pages SET show_on_home = 0 WHERE slug = 'politica-de-privacidade'");
                $addedPageCols = true;
            }
            if ($addedPageCols) {
                try {
                    Database::run('ALTER TABLE pages ADD KEY idx_pages_feed (status, show_on_home, published_at)');
                } catch (\Throwable) {
                }
            }
            if (!self::hasColumn('company_info', 'logo_url')) {
                Database::run("ALTER TABLE company_info ADD COLUMN logo_url VARCHAR(500) NOT NULL DEFAULT '' AFTER name");
            }
            if (!self::hasColumn('company_info', 'logo_display')) {
                Database::run("ALTER TABLE company_info ADD COLUMN logo_display ENUM('both','image','name') NOT NULL DEFAULT 'both' AFTER logo_url");
            }
            foreach (['legal_name' => 'VARCHAR(150)', 'cnpj' => 'VARCHAR(18)', 'legal_extra' => 'VARCHAR(300)'] as $col => $type) {
                if (!self::hasColumn('company_info', $col)) {
                    Database::run("ALTER TABLE company_info ADD COLUMN `$col` $type NOT NULL DEFAULT ''");
                }
            }
            Database::run('CREATE TABLE IF NOT EXISTS site_settings (
                skey VARCHAR(60) NOT NULL,
                svalue TEXT NOT NULL,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (skey)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
            self::markCurrent();
        } catch (\Throwable $e) {
            error_log('Falha na migração do banco: ' . $e->getMessage());
        }
    }

    private static function hasColumn(string $table, string $column): bool
    {
        return (int) Database::value(
            'SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$table, $column]
        ) > 0;
    }
}
