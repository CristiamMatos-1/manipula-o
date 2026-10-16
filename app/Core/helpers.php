<?php
declare(strict_types=1);

use App\Core\Config;
use App\Core\Csrf;
use App\Core\Rbac;

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** URL relativa à raiz do domínio, respeitando instalação em subpasta. */
function url(string $path = ''): string
{
    return Config::basePath() . '/' . ltrim($path, '/');
}

/** URL absoluta (e-mails, canonical, compartilhamento). */
function abs_url(string $path = ''): string
{
    return Config::appUrl() . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    $file = ROOT . '/public/assets/' . $path;
    return url('assets/' . $path) . '?v=' . (is_file($file) ? filemtime($file) : 1);
}

function redirect(string $path, int $code = 302): never
{
    header('Location: ' . (preg_match('#^https?://#', $path) ? $path : url($path)), true, $code);
    exit;
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(Csrf::token()) . '">';
}

function icon(string $name, string $class = 'h-5 w-5'): string
{
    static $icons = null;
    $icons ??= require APP_PATH . '/Core/icons.php';
    $paths = $icons[$name] ?? '';
    return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' . e($class) . '" aria-hidden="true">' . $paths . '</svg>';
}

function brand_icon(string $name, string $class = 'h-5 w-5'): string
{
    $paths = [
        'facebook' => 'M22 12a10 10 0 1 0-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.49-3.89 3.78-3.89 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.44 2.89h-2.34v6.99A10 10 0 0 0 22 12Z',
        'instagram' => 'M7.5 2h9A5.5 5.5 0 0 1 22 7.5v9a5.5 5.5 0 0 1-5.5 5.5h-9A5.5 5.5 0 0 1 2 16.5v-9A5.5 5.5 0 0 1 7.5 2Zm0 2A3.5 3.5 0 0 0 4 7.5v9A3.5 3.5 0 0 0 7.5 20h9a3.5 3.5 0 0 0 3.5-3.5v-9A3.5 3.5 0 0 0 16.5 4h-9ZM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10Zm0 2a3 3 0 1 0 0 6 3 3 0 0 0 0-6Zm5.25-3.5a1.25 1.25 0 1 1 0 2.5 1.25 1.25 0 0 1 0-2.5Z',
        'youtube' => 'M21.6 7.2a2.5 2.5 0 0 0-1.76-1.77C18.28 5 12 5 12 5s-6.28 0-7.84.43A2.5 2.5 0 0 0 2.4 7.2C2 8.77 2 12 2 12s0 3.23.4 4.8a2.5 2.5 0 0 0 1.76 1.77C5.72 19 12 19 12 19s6.28 0 7.84-.43a2.5 2.5 0 0 0 1.76-1.77C22 15.23 22 12 22 12s0-3.23-.4-4.8ZM10 15V9l5.2 3L10 15Z',
        'whatsapp' => 'M16.04 3C9.4 3 4 8.4 4 15.03c0 2.12.55 4.18 1.6 6L4 28l7.15-1.87a12.03 12.03 0 0 0 4.89 1.03h.01C22.7 27.16 28 21.76 28 15.13 28 11.9 26.74 8.88 24.46 6.6A11.9 11.9 0 0 0 16.04 3Zm0 21.99h-.01a10 10 0 0 1-5.1-1.4l-.37-.22-3.8 1 1.02-3.7-.24-.38a9.97 9.97 0 0 1-1.53-5.3C6.01 9.5 10.5 5 16.05 5c2.68 0 5.2 1.05 7.1 2.95a9.94 9.94 0 0 1 2.94 7.1c0 5.53-4.5 10.04-10.05 10.04Zm5.5-7.5c-.3-.15-1.78-.88-2.06-.98-.27-.1-.47-.15-.67.15-.2.3-.77.98-.94 1.18-.17.2-.35.22-.65.07-.3-.15-1.27-.47-2.42-1.5a9.07 9.07 0 0 1-1.68-2.08c-.18-.3-.02-.46.13-.61.14-.13.3-.35.45-.52.15-.18.2-.3.3-.5.1-.2.05-.38-.02-.53-.08-.15-.68-1.64-.93-2.25-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.5s1.07 2.9 1.22 3.1c.15.2 2.1 3.2 5.08 4.5.71.3 1.26.49 1.7.63.71.22 1.36.19 1.87.12.57-.09 1.78-.73 2.03-1.43.25-.7.25-1.3.17-1.43-.07-.12-.27-.2-.57-.35Z',
    ];
    $vb = $name === 'whatsapp' ? '0 0 32 32' : '0 0 24 24';
    return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="' . $vb . '" fill="currentColor" class="' . e($class) . '" aria-hidden="true"><path d="' . ($paths[$name] ?? '') . '"/></svg>';
}

function can(string $permission): bool
{
    $user = \App\Core\Auth::user();
    return $user !== null && Rbac::can($user['role'], $permission);
}

function format_date(?string $date, bool $withTime = false): string
{
    if (!$date) {
        return '-';
    }
    $ts = strtotime($date);
    return $ts ? date($withTime ? 'd/m/Y H:i' : 'd/m/Y', $ts) : '-';
}

function slugify(string $input): string
{
    $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $input) ?: $input;
    $s = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $s) ?? '');
    return substr(trim($s, '-'), 0, 80);
}

/** Aceita apenas http(s) - bloqueia javascript:, data: etc. */
function safe_url(?string $value): ?string
{
    if ($value === null || $value === '') {
        return null;
    }
    $parts = parse_url($value);
    if ($parts === false || !isset($parts['scheme'], $parts['host']) || !in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
        return null;
    }
    return $value;
}

/** Aceita URL http(s) ou caminho local começando com "/". */
function safe_image_url(?string $value): ?string
{
    if ($value === null || $value === '') {
        return null;
    }
    if (str_starts_with($value, '/') && !str_starts_with($value, '//')) {
        return url($value);
    }
    return safe_url($value);
}

function str_limit(string $s, int $max): string
{
    return mb_strlen($s) > $max ? mb_substr($s, 0, $max) : $s;
}

/** Campo de formulário padronizado. */
function field(string $label, string $name, array $o = []): string
{
    $type = $o['type'] ?? 'text';
    $value = $o['value'] ?? '';
    $error = $o['error'] ?? null;
    $hint = $o['hint'] ?? null;
    $extra = $o['attrs'] ?? '';
    $cls = 'input' . ($error ? ' border-red-500' : '');
    $wrap = $o['class'] ?? '';
    $html = '<div class="' . e($wrap) . '"><label for="' . e($name) . '" class="label">' . e($label) . '</label>';
    if ($type === 'textarea') {
        $html .= '<textarea id="' . e($name) . '" name="' . e($name) . '" rows="' . (int) ($o['rows'] ?? 3) . '" class="' . $cls . ' ' . e($o['inputClass'] ?? '') . '" ' . $extra . '>' . e($value) . '</textarea>';
    } else {
        $html .= '<input id="' . e($name) . '" name="' . e($name) . '" type="' . e($type) . '" value="' . ($type === 'password' ? '' : e($value)) . '" class="' . $cls . '" ' . $extra . '>';
    }
    if ($hint && !$error) {
        $html .= '<p class="mt-1 text-xs text-brand-navy/60">' . e($hint) . '</p>';
    }
    if ($error) {
        $html .= '<p role="alert" class="mt-1 text-xs font-medium text-red-600">' . e($error) . '</p>';
    }
    return $html . '</div>';
}

function submit_button(string $label, string $class = 'btn-primary', string $extra = ''): string
{
    return '<button type="submit" class="' . e($class) . '" data-loading ' . $extra . '>' . e($label) . '</button>';
}

function alert_box(?string $message, string $type = 'error'): string
{
    if (!$message) {
        return '';
    }
    $cls = $type === 'error' ? 'border-red-200 bg-red-50 text-red-700' : 'border-brand-green bg-brand-mint/50 text-brand-forest';
    return '<p role="' . ($type === 'error' ? 'alert' : 'status') . '" class="mb-4 rounded-lg border px-3 py-2 text-sm font-medium ' . $cls . '">' . e($message) . '</p>';
}
