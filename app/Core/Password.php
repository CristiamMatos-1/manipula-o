<?php
declare(strict_types=1);

namespace App\Core;

final class Password
{
    private const WEAK = ['admin', 'password', 'senha', '12345678', 'drogacare'];

    public static function hash(string $plain): string
    {
        return password_hash($plain, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public static function verify(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    /** Política: 10+ caracteres, maiúscula, minúscula e número. Retorna a mensagem de erro ou null. */
    public static function validate(string $p): ?string
    {
        if (mb_strlen($p) < 10) return 'A senha deve ter pelo menos 10 caracteres.';
        if (mb_strlen($p) > 128) return 'A senha deve ter no máximo 128 caracteres.';
        if (!preg_match('/[a-z]/', $p)) return 'Inclua ao menos uma letra minúscula.';
        if (!preg_match('/[A-Z]/', $p)) return 'Inclua ao menos uma letra maiúscula.';
        if (!preg_match('/[0-9]/', $p)) return 'Inclua ao menos um número.';
        if (in_array(strtolower($p), self::WEAK, true)) return 'Escolha uma senha menos previsível.';
        return null;
    }
}
