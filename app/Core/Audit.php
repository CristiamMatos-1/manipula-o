<?php
declare(strict_types=1);

namespace App\Core;

final class Audit
{
    public const LABELS = [
        'LOGIN' => 'Login realizado', 'LOGIN_FAILED' => 'Falha de login', 'LOGIN_LOCKED' => 'Conta bloqueada por tentativas',
        'LOGIN_BLOCKED' => 'Login bloqueado', 'LOGOUT' => 'Logout', 'SEED_ADMIN' => 'Admin inicial criado',
        'PASSWORD_RESET_REQUESTED' => 'Recuperação de senha solicitada', 'PASSWORD_RESET_COMPLETED' => 'Senha redefinida por link',
        'PASSWORD_CHANGED' => 'Senha alterada', 'PASSWORD_CHANGED_FORCED' => 'Senha inicial trocada',
        'USER_CREATE' => 'Usuário criado', 'USER_UPDATE' => 'Usuário editado', 'USER_DELETE' => 'Usuário excluído',
        'POST_CREATE' => 'Artigo criado', 'POST_UPDATE' => 'Artigo editado', 'POST_PUBLISH' => 'Artigo publicado',
        'POST_UPDATE_PUBLISHED' => 'Artigo publicado atualizado', 'POST_UNPUBLISH' => 'Artigo despublicado', 'POST_DELETE' => 'Artigo excluído',
        'PAGE_CREATE' => 'Página criada', 'PAGE_UPDATE' => 'Página editada', 'PAGE_PUBLISH' => 'Página publicada',
        'PAGE_UPDATE_PUBLISHED' => 'Página publicada atualizada', 'PAGE_UNPUBLISH' => 'Página despublicada', 'PAGE_DELETE' => 'Página excluída',
        'COMPANY_UPDATE' => 'Dados da empresa editados', 'SITE_UPDATE' => 'Página inicial editada', 'SITE_RESET' => 'Textos da página inicial restaurados',
    ];

    public static function label(string $action): string
    {
        return self::LABELS[$action] ?? $action;
    }

    /** Registra a ação na trilha de auditoria. NUNCA grave senhas ou tokens em $details. */
    public static function log(?array $user, string $action, string $entity, string|int|null $entityId = null, ?string $details = null, ?string $email = null): void
    {
        try {
            Database::run(
                'INSERT INTO audit_logs (user_id, user_email, action, entity, entity_id, details, ip, created_at) VALUES (?,?,?,?,?,?,?,NOW())',
                [
                    $user['id'] ?? null,
                    $user['email'] ?? $email,
                    $action,
                    $entity,
                    $entityId === null ? null : (string) $entityId,
                    $details === null ? null : mb_substr($details, 0, 500),
                    Request::ip(),
                ]
            );
        } catch (\Throwable $e) {
            error_log('Falha ao gravar auditoria: ' . $e->getMessage());
        }
    }
}
