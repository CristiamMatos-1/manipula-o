<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/** CompanyInfo é um singleton (id = 1). */
final class Company
{
    public const FIELDS = [
        'name', 'logo_url', 'logo_display', 'tagline', 'legal_name', 'cnpj', 'legal_extra', 'founded_year', 'pharmacist_name', 'pharmacist_crf', 'pharmacist_photo_url',
        'phone', 'whatsapp', 'email', 'address', 'maps_embed_url',
        'hero_video_url', 'youtube_url', 'instagram_url', 'facebook_url',
    ];

    private static ?array $cache = null;

    public static function get(): array
    {
        if (self::$cache === null) {
            Database::run('INSERT IGNORE INTO company_info (id) VALUES (1)');
            self::$cache = Database::one('SELECT * FROM company_info WHERE id = 1') ?? [];
        }
        return self::$cache;
    }

    public static function update(array $data): void
    {
        $sets = [];
        $params = [];
        foreach (self::FIELDS as $f) {
            if (array_key_exists($f, $data)) {
                $sets[] = "`$f` = ?";
                $params[] = $data[$f];
            }
        }
        Database::run('INSERT IGNORE INTO company_info (id) VALUES (1)');
        Database::run('UPDATE company_info SET ' . implode(', ', $sets) . ' WHERE id = 1', $params);
        self::$cache = null;
    }

    public static function yearsInBusiness(int $foundedYear): int
    {
        return max(0, (int) date('Y') - $foundedYear);
    }
}
