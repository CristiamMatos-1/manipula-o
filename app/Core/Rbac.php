<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Matriz de permissões (fonte única da verdade). Toda ação e página do painel
 * valida uma destas permissões no servidor.
 */
final class Rbac
{
    public const SUPERADMIN = 'SUPERADMIN';
    public const EDITOR = 'EDITOR_CONTEUDO';
    public const VIEWER = 'VISUALIZADOR';

    public const ROLES = [self::SUPERADMIN, self::EDITOR, self::VIEWER];

    public const LABELS = [
        self::SUPERADMIN => 'Super Administrador',
        self::EDITOR => 'Editor de Conteúdo',
        self::VIEWER => 'Visualizador',
    ];

    public const PERMISSIONS = [
        'dashboard:view' => [self::SUPERADMIN, self::EDITOR, self::VIEWER],
        'content:view' => [self::SUPERADMIN, self::EDITOR, self::VIEWER],
        'content:write' => [self::SUPERADMIN, self::EDITOR],
        'content:publish' => [self::SUPERADMIN, self::EDITOR],
        'content:delete' => [self::SUPERADMIN, self::EDITOR],
        'company:view' => [self::SUPERADMIN, self::EDITOR, self::VIEWER],
        'company:edit' => [self::SUPERADMIN, self::EDITOR],
        'users:manage' => [self::SUPERADMIN],
        'audit:view' => [self::SUPERADMIN],
    ];

    public static function can(?string $role, string $permission): bool
    {
        return $role !== null && in_array($role, self::PERMISSIONS[$permission] ?? [], true);
    }

    public static function label(string $role): string
    {
        return self::LABELS[$role] ?? $role;
    }
}
