<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\{Audit, Auth, Embed, Request};
use App\Models\Company;

final class CompanyController extends AdminController
{
    public function edit(): void
    {
        $user = Auth::requirePermission('company:view');
        $this->render('admin/company', ['title' => 'Dados da empresa', 'user' => $user, 'v' => Company::get(), 'errors' => []]);
    }

    public function update(): void
    {
        $user = Auth::requirePermission('company:edit');
        $v = [];
        foreach (Company::FIELDS as $f) {
            $v[$f] = Request::str($f);
        }
        $v['whatsapp'] = preg_replace('/\D/', '', $v['whatsapp']) ?? '';

        $e = [];
        $max = ['name' => 100, 'tagline' => 120, 'pharmacist_name' => 120, 'pharmacist_crf' => 40, 'phone' => 40, 'email' => 120, 'address' => 250];
        foreach ($max as $f => $m) {
            if (mb_strlen($v[$f]) > $m) $e[$f] = "Máximo de {$m} caracteres.";
        }
        if (mb_strlen($v['name']) < 2) $e['name'] = 'Informe o nome.';
        if (!ctype_digit($v['founded_year']) || (int) $v['founded_year'] < 1800 || (int) $v['founded_year'] > (int) date('Y')) $e['founded_year'] = 'Ano inválido.';
        if ($v['pharmacist_photo_url'] !== '' && !safe_image_url($v['pharmacist_photo_url'])) $e['pharmacist_photo_url'] = 'URL de foto inválida.';
        if ($v['whatsapp'] !== '' && strlen($v['whatsapp']) < 12) $e['whatsapp'] = 'Use DDI + DDD + número, ex.: 5511999999999.';
        if ($v['email'] !== '' && !filter_var($v['email'], FILTER_VALIDATE_EMAIL)) $e['email'] = 'E-mail inválido.';
        foreach (['maps_embed_url' => 'Mapa', 'hero_video_url' => 'Vídeo', 'youtube_url' => 'YouTube', 'instagram_url' => 'Instagram', 'facebook_url' => 'Facebook'] as $f => $label) {
            if ($v[$f] !== '' && (!safe_url($v[$f]) || mb_strlen($v[$f]) > 500)) $e[$f] = "{$label}: informe uma URL http(s) válida.";
        }
        if (!isset($e['hero_video_url']) && $v['hero_video_url'] !== '' && !Embed::video($v['hero_video_url'])) {
            $e['hero_video_url'] = 'Use um link do YouTube ou um arquivo .mp4/.webm.';
        }

        if ($e) {
            $this->render('admin/company', ['title' => 'Dados da empresa', 'user' => $user, 'v' => $v, 'errors' => $e], 'layouts/admin', 422);
            return;
        }

        $before = Company::get();
        $v['founded_year'] = (int) $v['founded_year'];
        Company::update($v);
        $changed = array_keys(array_filter($v, static fn ($val, $k) => (string) $before[$k] !== (string) $val, ARRAY_FILTER_USE_BOTH));
        Audit::log($user, 'COMPANY_UPDATE', 'CompanyInfo', '1', $changed ? 'Campos alterados: ' . implode(', ', $changed) : 'Sem alterações');
        $this->flashRedirect('success', 'Dados da empresa salvos e publicados no site.', '/admin/empresa');
    }
}
