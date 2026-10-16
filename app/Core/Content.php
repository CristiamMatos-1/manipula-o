<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Renderizador de conteúdo simples e SEGURO: todo texto é escapado (sem HTML bruto).
 * Sintaxe: "# título", "## subtítulo", "- item", **negrito**, [texto](https://link),
 * parágrafos separados por linha em branco.
 */
final class Content
{
    public static function render(string $content): string
    {
        $content = str_replace("\r\n", "\n", $content);
        $blocks = preg_split('/\n{2,}/', $content) ?: [];
        $html = '';
        foreach ($blocks as $block) {
            $block = trim($block, "\n");
            if (trim($block) === '') {
                continue;
            }
            $lines = explode("\n", $block);
            $allList = true;
            foreach ($lines as $l) {
                if (!preg_match('/^\s*-\s+/', $l)) { $allList = false; break; }
            }
            if ($allList) {
                $html .= '<ul class="list-disc space-y-1 pl-6">';
                foreach ($lines as $l) {
                    $html .= '<li>' . self::inline(preg_replace('/^\s*-\s+/', '', $l)) . '</li>';
                }
                $html .= '</ul>';
            } elseif (str_starts_with($block, '## ')) {
                $html .= '<h2 class="pt-2 text-xl font-bold text-brand-blue sm:text-2xl">' . self::inline(substr($block, 3)) . '</h2>';
            } elseif (str_starts_with($block, '# ')) {
                $html .= '<h2 class="pt-2 text-2xl font-extrabold text-brand-navy sm:text-3xl">' . self::inline(substr($block, 2)) . '</h2>';
            } else {
                $html .= '<p>' . implode('<br>', array_map([self::class, 'inline'], $lines)) . '</p>';
            }
        }
        return '<div class="space-y-4 text-base leading-relaxed text-brand-navy/90 sm:text-lg">' . $html . '</div>';
    }

    private static function inline(string $text): string
    {
        $out = '';
        $last = 0;
        preg_match_all('/\*\*(.+?)\*\*|\[([^\]]+)\]\(([^)\s]+)\)/u', $text, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        foreach ($matches as $m) {
            $start = $m[0][1];
            $out .= e(substr($text, $last, $start - $last));
            if (isset($m[1]) && $m[1][1] !== -1 && $m[1][0] !== '') {
                $out .= '<strong>' . e($m[1][0]) . '</strong>';
            } else {
                $href = safe_url($m[3][0] ?? '');
                $label = e($m[2][0] ?? '');
                $out .= $href ? '<a href="' . e($href) . '" target="_blank" rel="noopener noreferrer nofollow" class="text-brand-blue underline">' . $label . '</a>' : $label;
            }
            $last = $start + strlen($m[0][0]);
        }
        return $out . e(substr($text, $last));
    }
}
