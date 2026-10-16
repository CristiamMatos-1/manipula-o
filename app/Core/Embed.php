<?php
declare(strict_types=1);

namespace App\Core;

final class Embed
{
    /** @return array{kind:string,src:string}|null */
    public static function video(?string $raw): ?array
    {
        $url = safe_url($raw);
        if (!$url) {
            return null;
        }
        $u = parse_url($url);
        $host = preg_replace('/^www\./', '', strtolower($u['host'] ?? ''));
        $path = $u['path'] ?? '';
        $id = null;
        if ($host === 'youtu.be') {
            $id = ltrim($path, '/');
        } elseif (in_array($host, ['youtube.com', 'm.youtube.com', 'youtube-nocookie.com'], true)) {
            if ($path === '/watch') {
                parse_str($u['query'] ?? '', $q);
                $id = is_string($q['v'] ?? null) ? $q['v'] : null;
            } elseif (preg_match('#^/(embed|shorts)/([^/]+)#', $path, $m)) {
                $id = $m[2];
            }
        }
        if ($id !== null && preg_match('/^[\w-]{6,20}$/', $id)) {
            // youtube-nocookie: não grava cookies até o usuário interagir (LGPD).
            return ['kind' => 'youtube', 'src' => 'https://www.youtube-nocookie.com/embed/' . $id . '?rel=0&modestbranding=1'];
        }
        if (preg_match('/\.(mp4|webm|ogg)$/i', $path)) {
            return ['kind' => 'file', 'src' => $url];
        }
        return null;
    }

    /**
     * Vídeos ativos do Hero: link (YouTube ou .mp4 externo) e/ou arquivo enviado.
     * @return list<array{kind:string,src:string,origin:string}>
     */
    public static function heroVideos(array $company): array
    {
        $list = [];
        if ($v = self::video($company['hero_video_url'] ?? '')) {
            $list[] = $v + ['origin' => 'link'];
        }
        $file = (string) ($company['hero_video_upload'] ?? '');
        if ($file !== '' && preg_match('#^/uploads/videos/[a-f0-9]{24}\.(mp4|webm)$#', $file) && is_file(Upload::dir() . substr($file, strlen('/uploads')))) {
            $list[] = ['kind' => 'file', 'src' => static_url($file), 'origin' => 'upload'];
        }
        return $list;
    }

    /** Com os dois ativos, sorteia um a cada acesso. */
    public static function heroVideo(array $company): ?array
    {
        $list = self::heroVideos($company);
        return $list === [] ? null : $list[random_int(0, count($list) - 1)];
    }

    /** Só aceita incorporação oficial do Google Maps; senão gera pelo endereço. */
    public static function maps(?string $raw, string $address): ?string
    {
        $url = safe_url($raw);
        if ($url) {
            $u = parse_url($url);
            $host = strtolower($u['host'] ?? '');
            if (($u['scheme'] ?? '') === 'https' && ($host === 'google.com' || str_ends_with($host, '.google.com')) && str_starts_with($u['path'] ?? '', '/maps')) {
                return $url;
            }
        }
        return trim($address) !== '' ? 'https://www.google.com/maps?q=' . rawurlencode($address) . '&output=embed' : null;
    }

    public static function whatsapp(string $number, ?string $text = null): ?string
    {
        $digits = preg_replace('/\D/', '', $number) ?? '';
        if (strlen($digits) < 10) {
            return null;
        }
        return 'https://wa.me/' . $digits . ($text ? '?text=' . rawurlencode($text) : '');
    }
}
