<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\{Audit, Auth, Database, Password, Rbac, Request, Router};

final class UsersController extends AdminController
{
    public function index(): void
    {
        $me = Auth::requirePermission('users:manage');
        $users = Database::all("SELECT * FROM users ORDER BY FIELD(role,'SUPERADMIN','EDITOR_CONTEUDO','VISUALIZADOR'), name");
        $this->render('admin/users_list', ['title' => 'Usuários', 'user' => $me, 'users' => $users]);
    }

    public function create(): void
    {
        $me = Auth::requirePermission('users:manage');
        $this->render('admin/user_form', ['title' => 'Novo usuário', 'user' => $me, 'target' => null, 'v' => [], 'errors' => [], 'error' => null]);
    }

    public function store(): void
    {
        $this->save(null);
    }

    public function edit(array $p): void
    {
        $me = Auth::requirePermission('users:manage');
        $target = Database::one('SELECT * FROM users WHERE id = ?', [(int) $p['id']]);
        if (!$target) {
            (new Router())->notFound();
            return;
        }
        $this->render('admin/user_form', ['title' => $target['name'], 'user' => $me, 'target' => $target, 'v' => [], 'errors' => [], 'error' => null]);
    }

    public function update(array $p): void
    {
        $this->save((int) $p['id']);
    }

    /** Garante que sempre reste ao menos um SUPERADMIN ativo. */
    private function otherActiveSuperadmins(int $excludeId): int
    {
        return (int) Database::value("SELECT COUNT(*) FROM users WHERE role = 'SUPERADMIN' AND active = 1 AND id <> ?", [$excludeId]);
    }

    private function save(?int $id): void
    {
        $actor = Auth::requirePermission('users:manage');
        $v = [
            'name' => Request::str('name'),
            'email' => strtolower(Request::str('email')),
            'role' => Request::str('role'),
            'password' => Request::raw('password'),
            'active' => isset($_POST['active']),
            'must_change_password' => isset($_POST['must_change_password']),
        ];
        $target = $id ? Database::one('SELECT * FROM users WHERE id = ?', [$id]) : null;
        if ($id && !$target) {
            (new Router())->notFound();
            return;
        }

        $errors = [];
        $error = null;
        if (mb_strlen($v['name']) < 2 || mb_strlen($v['name']) > 100) $errors['name'] = 'Informe o nome (2 a 100 caracteres).';
        if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL) || strlen($v['email']) > 190) $errors['email'] = 'E-mail inválido.';
        if (!in_array($v['role'], Rbac::ROLES, true)) $errors['role'] = 'Perfil inválido.';
        if (!$id || $v['password'] !== '') {
            if ($e = Password::validate($v['password'])) $errors['password'] = $e;
        }
        if (!$errors) {
            $clash = Database::one('SELECT id FROM users WHERE email = ?', [$v['email']]);
            if ($clash && (int) $clash['id'] !== $id) $errors['email'] = 'Já existe um usuário com este e-mail.';
        }

        if (!$errors && $id && $target) {
            $active = $id === (int) $actor['id'] ? true : $v['active'];
            $losesSuper = $target['role'] === Rbac::SUPERADMIN && ($v['role'] !== Rbac::SUPERADMIN || !$active);
            if ($losesSuper && $this->otherActiveSuperadmins($id) === 0) {
                $error = 'Não é possível: este é o único Super Administrador ativo do sistema.';
            }
            $v['active'] = $active;
        }

        if ($errors || $error) {
            $this->render('admin/user_form', ['title' => $target['name'] ?? 'Novo usuário', 'user' => $actor, 'target' => $target, 'v' => $v, 'errors' => $errors, 'error' => $error], 'layouts/admin', 422);
            return;
        }

        if (!$id) {
            $newId = Database::insert(
                'INSERT INTO users (name, email, password_hash, role, active, must_change_password, created_at, updated_at) VALUES (?,?,?,?,1,1,NOW(),NOW())',
                [$v['name'], $v['email'], Password::hash($v['password']), $v['role']]
            );
            Audit::log($actor, 'USER_CREATE', 'User', $newId, $v['email'] . ' (' . Rbac::label($v['role']) . ')');
            $this->flashRedirect('success', 'Usuário criado. Ele deverá trocar a senha no primeiro acesso.', '/admin/usuarios');
        }

        $changes = [];
        if ($target['name'] !== $v['name']) $changes[] = 'nome';
        if ($target['email'] !== $v['email']) $changes[] = "e-mail ({$target['email']} -> {$v['email']})";
        if ($target['role'] !== $v['role']) $changes[] = 'perfil (' . Rbac::label($target['role']) . ' -> ' . Rbac::label($v['role']) . ')';
        if ((bool) $target['active'] !== $v['active']) $changes[] = $v['active'] ? 'reativado' : 'desativado';
        if ($v['password'] !== '') $changes[] = 'senha redefinida por administrador';

        $params = [$v['name'], $v['email'], $v['role'], $v['active'] ? 1 : 0, ($v['password'] !== '' || $v['must_change_password']) ? 1 : 0];
        $sql = 'UPDATE users SET name = ?, email = ?, role = ?, active = ?, must_change_password = ?, updated_at = NOW()';
        if ($v['password'] !== '') {
            $sql .= ', password_hash = ?, reset_token = NULL, reset_token_expiry = NULL, failed_login_attempts = 0, locked_until = NULL';
            $params[] = Password::hash($v['password']);
        }
        if (!$v['active']) {
            $sql .= ', reset_token = NULL, reset_token_expiry = NULL';
        }
        $params[] = $id;
        Database::run($sql . ' WHERE id = ?', $params);
        Audit::log($actor, 'USER_UPDATE', 'User', $id, $v['email'] . ': ' . ($changes ? implode('; ', $changes) : 'sem alterações'));
        $this->flashRedirect('success', 'Usuário atualizado.', '/admin/usuarios/' . $id);
    }

    public function destroy(array $p): void
    {
        $actor = Auth::requirePermission('users:manage');
        $id = (int) $p['id'];
        if ($id === (int) $actor['id']) {
            $this->flashRedirect('error', 'Você não pode excluir a própria conta.', '/admin/usuarios');
        }
        $target = Database::one('SELECT * FROM users WHERE id = ?', [$id]);
        if (!$target) {
            redirect('/admin/usuarios');
        }
        if ($target['role'] === Rbac::SUPERADMIN && $this->otherActiveSuperadmins($id) === 0) {
            $this->flashRedirect('error', 'Não é possível excluir o único Super Administrador ativo.', '/admin/usuarios');
        }
        Database::run('DELETE FROM users WHERE id = ?', [$id]);
        Audit::log($actor, 'USER_DELETE', 'User', $id, $target['email'] . ' (' . Rbac::label($target['role']) . ')');
        $this->flashRedirect('success', 'Usuário excluído.', '/admin/usuarios');
    }
}
