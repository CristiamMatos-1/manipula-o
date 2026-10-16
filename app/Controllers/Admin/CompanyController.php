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
        $before = Company::get();
        $v['logo_display'] = $v['logo_display'] === '' ? ($before['logo_display'] ?? 'both') : $v['logo_display'];
        if (!in_array($v['logo_display'], ['both', 'image', 'name'], true)) $e['logo_display'] = 'Opção inválida.';
        $max = ['name' => 100, 'tagline' => 120, 'legal_name' => 150, 'legal_extra' => 300, 'pharmacist_name' => 120, 'pharmacist_crf' => 40, 'phone' => 40, 'email' => 120, 'address' => 250];
        foreach ($max as $f => $m) {
            if (mb_strlen($v[$f]) > $m) $e[$f] = "Máximo de {$m} caracteres.";
        }
        if ($v['cnpj'] !== '') {
            $digits = preg_replace('/\D/', '', $v['cnpj']) ?? '';
            if (!self::validCnpj($digits)) {
                $e['cnpj'] = 'CNPJ inválido. Confira os 14 números.';
            } else {
                $v['cnpj'] = vsprintf('%s%s.%s%s%s.%s%s%s/%s%s%s%s-%s%s', str_split($digits));
            }
        }
        if (mb_strlen($v['name']) < 2) $e['name'] = 'Informe o nome.';
        if (!ctype_digit($v['founded_year']) || (int) $v['founded_year'] < 1800 || (int) $v['founded_year'] > (int) date('Y')) $e['founded_year'] = 'Ano inválido.';
        if ($v['whatsapp'] !== '' && strlen($v['whatsapp']) < 12) $e['whatsapp'] = 'Use DDI + DDD + número, ex.: 5511999999999.';
        if ($v['email'] !== '' && !filter_var($v['email'], FILTER_VALIDATE_EMAIL)) $e['email'] = 'E-mail inválido.';
        foreach (['maps_embed_url' => 'Mapa', 'hero_video_url' => 'Vídeo', 'youtube_url' => 'YouTube', 'instagram_url' => 'Instagram', 'facebook_url' => 'Facebook'] as $f => $label) {
            if ($v[$f] !== '' && (!safe_url($v[$f]) || mb_strlen($v[$f]) > 500)) $e[$f] = "{$label}: informe uma URL http(s) válida.";
        }
        if (!isset($e['hero_video_url']) && $v['hero_video_url'] !== '' && !Embed::video($v['hero_video_url'])) {
            $e['hero_video_url'] = 'Use um link do YouTube (ou o endereço direto de um arquivo .mp4/.webm).';
        }

        $oldLogo = (string) ($before['logo_url'] ?? '');
        $oldPhoto = (string) ($before['pharmacist_photo_url'] ?? '');
        $oldVideo = (string) ($before['hero_video_upload'] ?? '');
        if (!$e) {
            $v['logo_url'] = (string) $this->imageValue('logo_url', $oldLogo, $e);
            $v['pharmacist_photo_url'] = (string) $this->imageValue('pharmacist_photo_url', $oldPhoto, $e);
            $v['hero_video_upload'] = (string) $this->videoValue('hero_video_upload', $oldVideo, $e);
            if (!isset($e['logo_url']) && $v['logo_display'] === 'image' && $v['logo_url'] === '') {
                $e['logo_display'] = 'Para mostrar só a imagem, anexe a logo da empresa.';
            }
        }
        if ($e) {
            $this->discardReplacedImage($v['logo_url'] ?? null, $oldLogo);
            $this->discardReplacedImage($v['pharmacist_photo_url'] ?? null, $oldPhoto);
            $this->discardReplacedImage($v['hero_video_upload'] ?? null, $oldVideo);
            $v['hero_video_upload'] = $oldVideo;
            $v['logo_url'] = $oldLogo;
            $v['pharmacist_photo_url'] = $oldPhoto;
            $this->render('admin/company', ['title' => 'Dados da empresa', 'user' => $user, 'v' => $v, 'errors' => $e], 'layouts/admin', 422);
            return;
        }

        $v['founded_year'] = (int) $v['founded_year'];
        Company::update($v);
        $this->discardReplacedImage($oldLogo, $v['logo_url']);
        $this->discardReplacedImage($oldPhoto, $v['pharmacist_photo_url']);
        $this->discardReplacedImage($oldVideo, $v['hero_video_upload']);
        $changed = array_keys(array_filter($v, static fn ($val, $k) => (string) $before[$k] !== (string) $val, ARRAY_FILTER_USE_BOTH));
        Audit::log($user, 'COMPANY_UPDATE', 'CompanyInfo', '1', $changed ? 'Campos alterados: ' . implode(', ', $changed) : 'Sem alterações');
        $this->flashRedirect('success', 'Dados da empresa salvos e publicados no site.', '/admin/empresa');
    }

    private static function validCnpj(string $d): bool
    {
        if (strlen($d) !== 14 || preg_match('/^(\d)\1{13}$/', $d)) {
            return false;
        }
        foreach ([12, 13] as $len) {
            $sum = 0;
            $weight = $len - 7;
            for ($i = 0; $i < $len; $i++) {
                $sum += (int) $d[$i] * $weight;
                $weight = $weight === 2 ? 9 : $weight - 1;
            }
            $dv = $sum % 11 < 2 ? 0 : 11 - $sum % 11;
            if ((int) $d[$len] !== $dv) {
                return false;
            }
        }
        return true;
    }
}
