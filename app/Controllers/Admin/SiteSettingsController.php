<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\{Audit, Auth, Request};
use App\Models\Settings;

final class SiteSettingsController extends AdminController
{
    public function edit(): void
    {
        $user = Auth::requirePermission('company:view');
        $v = [];
        foreach (Settings::DEFS as $key => $def) {
            $v[$key] = Settings::raw($key);
        }
        $this->render('admin/site', ['title' => 'Página inicial', 'user' => $user, 'v' => $v, 'errors' => []]);
    }

    public function update(): void
    {
        $user = Auth::requirePermission('company:edit');

        if (Request::str('reset') === '1') {
            Settings::resetTexts();
            Audit::log($user, 'SITE_RESET', 'SiteSettings', null, 'Textos da página inicial restaurados ao padrão');
            $this->flashRedirect('success', 'Textos restaurados ao padrão. O banner não foi alterado.', '/admin/pagina-inicial');
        }

        $v = [];
        $errors = [];
        foreach (Settings::DEFS as $key => $def) {
            if ($def['type'] === 'image') {
                continue;
            }
            $v[$key] = $def['type'] === 'textarea' ? trim(str_replace("\r\n", "\n", Request::raw($key))) : Request::str($key);
            if (mb_strlen($v[$key]) > ($def['max'] ?? 255)) {
                $errors[$key] = 'Máximo de ' . ($def['max'] ?? 255) . ' caracteres.';
            }
            if ($def['type'] === 'url' && $v[$key] !== '' && !self::validLink($v[$key])) {
                $errors[$key] = 'Use https://..., um endereço do site começando com / ou um âncora começando com #.';
            }
        }

        $oldBanner = Settings::raw('banner_image');
        $v['banner_image'] = $oldBanner;
        if (!$errors) {
            $v['banner_image'] = (string) $this->imageValue('banner_image', $oldBanner, $errors);
        }
        if ($errors) {
            if (($v['banner_image'] ?? '') !== $oldBanner) {
                $this->discardReplacedImage($v['banner_image'], $oldBanner);
            }
            $v['banner_image'] = $oldBanner;
            $this->render('admin/site', ['title' => 'Página inicial', 'user' => $user, 'v' => $v, 'errors' => $errors], 'layouts/admin', 422);
            return;
        }

        $changed = [];
        foreach ($v as $key => $val) {
            if (Settings::raw($key) !== (string) $val) {
                $changed[] = $key;
            }
        }
        Settings::save($v);
        $this->discardReplacedImage($oldBanner, $v['banner_image']);
        Audit::log($user, 'SITE_UPDATE', 'SiteSettings', null, $changed ? 'Campos alterados: ' . implode(', ', $changed) : 'Sem alterações');
        $this->flashRedirect('success', 'Página inicial salva e publicada no site.', '/admin/pagina-inicial');
    }

    public static function validLink(string $url): bool
    {
        if (str_starts_with($url, '#')) {
            return preg_match('/^#[\w-]*$/', $url) === 1;
        }
        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            return preg_match('#^/[\w\-./?=&%\#]*$#', $url) === 1;
        }
        return safe_url($url) !== null;
    }
}
