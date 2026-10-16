<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Upload seguro de imagens: valida o conteúdo real (não confia em nome/Content-Type),
 * aceita só JPG/PNG/WebP/GIF, nomeia com valor aleatório, reduz imagens muito grandes
 * e (com GD) regrava o arquivo, removendo metadados e qualquer conteúdo embutido.
 */
final class Upload
{
    public const MAX_BYTES = 8 * 1024 * 1024;
    public const MAX_DIMENSION = 1600;
    private const TYPES = [
        'image/jpeg' => ['jpg', IMAGETYPE_JPEG],
        'image/png' => ['png', IMAGETYPE_PNG],
        'image/webp' => ['webp', IMAGETYPE_WEBP],
        'image/gif' => ['gif', IMAGETYPE_GIF],
    ];

    public static function dir(): string
    {
        return ROOT . '/public/uploads';
    }

    /**
     * @return array{0:?string,1:?string} [caminho público (ex.: /uploads/2026/ab12.jpg) ou null se nenhum arquivo enviado, erro ou null]
     */
    public static function image(string $input): array
    {
        $f = $_FILES[$input] ?? null;
        if (!is_array($f) || !isset($f['error']) || is_array($f['error'])) {
            return [null, null];
        }
        if ($f['error'] === UPLOAD_ERR_NO_FILE) {
            return [null, null];
        }
        if ($f['error'] !== UPLOAD_ERR_OK) {
            return [null, match ($f['error']) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Imagem maior que o limite do servidor. Envie uma imagem menor (ideal: até 2 MB).',
                UPLOAD_ERR_PARTIAL => 'O envio foi interrompido. Tente novamente.',
                default => 'Não foi possível enviar a imagem (código ' . (int) $f['error'] . ').',
            }];
        }
        $tmp = (string) $f['tmp_name'];
        if (!is_uploaded_file($tmp) && !(PHP_SAPI === 'cli')) {
            return [null, 'Arquivo de envio inválido.'];
        }
        if ((int) $f['size'] > self::MAX_BYTES) {
            return [null, 'A imagem pode ter no máximo 8 MB.'];
        }

        $info = @getimagesize($tmp);
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($tmp) ?: '';
        if ($info === false || !isset(self::TYPES[$mime]) || $info[2] !== self::TYPES[$mime][1]) {
            return [null, 'Formato não aceito. Envie uma imagem JPG, PNG, WebP ou GIF.'];
        }
        [$w, $h] = [(int) $info[0], (int) $info[1]];
        if ($w < 1 || $h < 1 || $w > 12000 || $h > 12000) {
            return [null, 'Dimensões da imagem fora do permitido.'];
        }

        $raw = (string) @file_get_contents($tmp);
        if (stripos($raw, '<?php') !== false || str_contains($raw, '<?=')) {
            return [null, 'Arquivo suspeito: contém código. Envie apenas imagens.'];
        }
        unset($raw);

        $ext = self::TYPES[$mime][0];
        $sub = date('Y');
        $dir = self::dir() . '/' . $sub;
        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            return [null, 'Não foi possível criar a pasta public/uploads. Verifique a permissão de escrita.'];
        }
        $name = bin2hex(random_bytes(12)) . '.' . $ext;
        $dest = $dir . '/' . $name;

        if (!self::reencode($tmp, $dest, $mime, $w, $h)) {
            $moved = PHP_SAPI === 'cli' ? @copy($tmp, $dest) : @move_uploaded_file($tmp, $dest);
            if (!$moved) {
                return [null, 'Não foi possível salvar a imagem. Verifique a permissão de escrita em public/uploads.'];
            }
        }
        @chmod($dest, 0644);
        return ['/uploads/' . $sub . '/' . $name, null];
    }

    public static function delete(?string $url): void
    {
        if ($url === null || !preg_match('#^/uploads/\d{4}/[a-f0-9]{24}\.(jpg|png|webp|gif)$#', $url)) {
            return;
        }
        $file = self::dir() . substr($url, strlen('/uploads'));
        if (is_file($file)) {
            @unlink($file);
        }
    }

    private static function reencode(string $src, string $dest, string $mime, int $w, int $h): bool
    {
        if (!function_exists('imagecreatetruecolor') || $mime === 'image/gif') {
            return false;
        }
        $create = match ($mime) {
            'image/jpeg' => 'imagecreatefromjpeg',
            'image/png' => 'imagecreatefrompng',
            'image/webp' => 'imagecreatefromwebp',
            default => null,
        };
        if ($create === null || !function_exists($create)) {
            return false;
        }
        $img = @$create($src);
        if (!$img) {
            return false;
        }
        if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
            $exif = @exif_read_data($src);
            $rot = match ((int) ($exif['Orientation'] ?? 1)) { 3 => 180, 6 => -90, 8 => 90, default => 0 };
            if ($rot !== 0 && ($r = imagerotate($img, $rot, 0))) {
                $img = $r;
            }
        }
        $w = imagesx($img);
        $h = imagesy($img);
        $scale = min(1.0, self::MAX_DIMENSION / max($w, $h));
        if ($scale < 1.0) {
            $nw = max(1, (int) round($w * $scale));
            $nh = max(1, (int) round($h * $scale));
            $out = imagecreatetruecolor($nw, $nh);
            if ($mime !== 'image/jpeg') {
                imagealphablending($out, false);
                imagesavealpha($out, true);
            }
            imagecopyresampled($out, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
            $img = $out;
        } elseif ($mime !== 'image/jpeg') {
            imagealphablending($img, false);
            imagesavealpha($img, true);
        }
        return match ($mime) {
            'image/jpeg' => imagejpeg($img, $dest, 85),
            'image/png' => imagepng($img, $dest, 6),
            'image/webp' => function_exists('imagewebp') && imagewebp($img, $dest, 85),
            default => false,
        };
    }
}
