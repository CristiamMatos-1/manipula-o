<?php
declare(strict_types=1);

namespace App\Core;

final class Auth
{
    private static ?array $user = null;
    private static bool $loaded = false;

    /** Usuário logado, SEMPRE recarregado do banco (desativação/mudança de perfil valem na hora). */
    public static function user(): ?array
    {
        if (self::$loaded) {
            return self::$user;
        }
        self::$loaded = true;
        if (session_status() !== PHP_SESSION_ACTIVE || empty($_SESSION['uid'])) {
            return null;
        }
        $user = Database::one(
            'SELECT id, name, email, role, active, must_change_password FROM users WHERE id = ?',
            [(int) $_SESSION['uid']]
        );
        if (!$user || !(int) $user['active']) {
            unset($_SESSION['uid']);
            return null;
        }
        return self::$user = $user;
    }

    public static function login(int $userId): void
    {
        Session::regenerate();
        $_SESSION['uid'] = $userId;
        self::$loaded = false;
        self::$user = null;
    }

    public static function logout(): void
    {
        Session::destroy();
        self::$loaded = false;
        self::$user = null;
    }

    /** Exige login. Com troca de senha pendente, envia à tela de troca. */
    public static function require(bool $allowMustChange = false): array
    {
        $user = self::user();
        if (!$user) {
            redirect('/admin/login');
        }
        if ((int) $user['must_change_password'] && !$allowMustChange) {
            redirect('/admin/alterar-senha');
        }
        return $user;
    }

    public static function requirePermission(string $permission): array
    {
        $user = self::require();
        if (!Rbac::can($user['role'], $permission)) {
            redirect('/admin/acesso-negado');
        }
        return $user;
    }
}
