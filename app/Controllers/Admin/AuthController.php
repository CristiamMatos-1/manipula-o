<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\{Audit, Auth, Database, Mailer, Password, Request, Session};

final class AuthController extends AdminController
{
    private const MAX_ATTEMPTS = 5;
    private const LOCK_MINUTES = 15;
    private const IP_MAX_FAILURES = 20;
    private const RESET_MINUTES = 30;
    // Hash fictício: iguala o tempo de resposta quando o e-mail não existe.
    private const DUMMY_HASH = '$2y$12$8bbFSFFIX5tpQlP8.MMTr.fpAfbDKGJ/xjaWxskqeUb.u76lz1.YG';

    public function loginForm(): void
    {
        if (Auth::user()) {
            redirect('/admin');
        }
        $this->render('auth/login', ['title' => 'Entrar', 'errors' => [], 'error' => null, 'email' => '', 'reset' => Request::query('reset') !== ''], 'layouts/auth');
    }

    public function login(): void
    {
        $email = strtolower(Request::str('email'));
        $password = Request::raw('password');
        $render = fn (?string $err, array $fe = []) => $this->render('auth/login', ['title' => 'Entrar', 'errors' => $fe, 'error' => $err, 'email' => $email, 'reset' => false], 'layouts/auth', 422);

        $errors = [];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Informe um e-mail válido.';
        if ($password === '') $errors['password'] = 'Informe a senha.';
        if ($errors) {
            $render(null, $errors);
            return;
        }

        $ip = Request::ip();
        if ($ip && (int) Database::value("SELECT COUNT(*) FROM audit_logs WHERE ip = ? AND action IN ('LOGIN_FAILED','LOGIN_LOCKED') AND created_at > (NOW() - INTERVAL 15 MINUTE)", [$ip]) >= self::IP_MAX_FAILURES) {
            Audit::log(null, 'LOGIN_BLOCKED', 'Auth', null, 'Muitas falhas vindas do mesmo IP', $email);
            $render('Muitas tentativas. Tente novamente em alguns minutos.');
            return;
        }

        $generic = 'E-mail ou senha incorretos.';
        $user = Database::one('SELECT * FROM users WHERE email = ?', [$email]);
        if (!$user) {
            Password::verify($password, self::DUMMY_HASH);
            Audit::log(null, 'LOGIN_FAILED', 'Auth', null, 'E-mail desconhecido', $email);
            $render($generic);
            return;
        }

        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            Audit::log($user, 'LOGIN_BLOCKED', 'Auth', null, 'Conta temporariamente bloqueada');
            $render('Muitas tentativas. Tente novamente em alguns minutos.');
            return;
        }

        if (!Password::verify($password, $user['password_hash']) || !(int) $user['active']) {
            $attempts = (int) $user['failed_login_attempts'] + 1;
            $lock = $attempts >= self::MAX_ATTEMPTS;
            Database::run('UPDATE users SET failed_login_attempts = ?, locked_until = ? WHERE id = ?', [
                $lock ? 0 : $attempts,
                $lock ? date('Y-m-d H:i:s', time() + self::LOCK_MINUTES * 60) : null,
                $user['id'],
            ]);
            Audit::log($user, $lock ? 'LOGIN_LOCKED' : 'LOGIN_FAILED', 'Auth', null, !(int) $user['active'] ? 'Usuário inativo' : 'Tentativa ' . $attempts . '/' . self::MAX_ATTEMPTS);
            $render($generic);
            return;
        }

        Database::run('UPDATE users SET failed_login_attempts = 0, locked_until = NULL, last_login_at = NOW() WHERE id = ?', [$user['id']]);
        Auth::login((int) $user['id']);
        Audit::log($user, 'LOGIN', 'Auth');
        redirect((int) $user['must_change_password'] ? '/admin/alterar-senha' : '/admin');
    }

    public function logout(): void
    {
        $user = Auth::user();
        if ($user) {
            Audit::log($user, 'LOGOUT', 'Auth');
        }
        Auth::logout();
        redirect('/admin/login');
    }

    public function forgotForm(): void
    {
        $this->render('auth/forgot', ['title' => 'Esqueci minha senha', 'errors' => [], 'sent' => false, 'email' => ''], 'layouts/auth');
    }

    public function forgot(): void
    {
        $email = strtolower(Request::str('email'));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->render('auth/forgot', ['title' => 'Esqueci minha senha', 'errors' => ['email' => 'Informe um e-mail válido.'], 'sent' => false, 'email' => $email], 'layouts/auth', 422);
            return;
        }

        $user = Database::one('SELECT * FROM users WHERE email = ? AND active = 1', [$email]);
        if ($user) {
            $token = bin2hex(random_bytes(32));
            Database::run('UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?', [
                hash('sha256', $token),
                date('Y-m-d H:i:s', time() + self::RESET_MINUTES * 60),
                $user['id'],
            ]);
            [$text, $html] = Mailer::passwordReset($user['name'], abs_url('/admin/redefinir-senha?token=' . $token), self::RESET_MINUTES);
            Mailer::send($user['email'], 'Redefinição de senha - Drogacare', $text, $html);
            Audit::log($user, 'PASSWORD_RESET_REQUESTED', 'Auth');
        }

        // Resposta idêntica exista o e-mail ou não (evita enumeração de usuários).
        $this->render('auth/forgot', ['title' => 'Esqueci minha senha', 'errors' => [], 'sent' => true, 'email' => ''], 'layouts/auth');
    }

    private function userByToken(string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }
        return Database::one('SELECT * FROM users WHERE reset_token = ? AND reset_token_expiry > NOW() AND active = 1', [hash('sha256', $token)]);
    }

    public function resetForm(): void
    {
        $token = Request::query('token');
        if (!$this->userByToken($token)) {
            $this->render('auth/reset_invalid', ['title' => 'Link inválido'], 'layouts/auth', 410);
            return;
        }
        $this->render('auth/reset', ['title' => 'Criar nova senha', 'token' => $token, 'errors' => []], 'layouts/auth');
    }

    public function reset(): void
    {
        $token = Request::str('token');
        $user = $this->userByToken($token);
        if (!$user) {
            $this->render('auth/reset_invalid', ['title' => 'Link inválido'], 'layouts/auth', 410);
            return;
        }
        $password = Request::raw('password');
        $errors = [];
        if ($e = Password::validate($password)) $errors['password'] = $e;
        if ($password !== Request::raw('confirm')) $errors['confirm'] = 'As senhas não conferem.';
        if ($errors) {
            $this->render('auth/reset', ['title' => 'Criar nova senha', 'token' => $token, 'errors' => $errors], 'layouts/auth', 422);
            return;
        }

        Database::run('UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expiry = NULL, must_change_password = 0, failed_login_attempts = 0, locked_until = NULL, updated_at = NOW() WHERE id = ?', [Password::hash($password), $user['id']]);
        Audit::log($user, 'PASSWORD_RESET_COMPLETED', 'Auth');
        Auth::logout();
        redirect('/admin/login?reset=1');
    }

    public function changeForm(): void
    {
        $user = Auth::require(true);
        $this->render('auth/change', ['title' => 'Alterar senha', 'forced' => (bool) $user['must_change_password'], 'errors' => []], 'layouts/auth');
    }

    public function change(): void
    {
        $user = Auth::require(true);
        $forced = (bool) $user['must_change_password'];
        $current = Request::raw('current');
        $password = Request::raw('password');
        $errors = [];

        $row = Database::one('SELECT password_hash FROM users WHERE id = ?', [$user['id']]);
        if ($current === '') $errors['current'] = 'Informe a senha atual.';
        elseif (!Password::verify($current, $row['password_hash'])) $errors['current'] = 'Senha atual incorreta.';
        if ($e = Password::validate($password)) $errors['password'] = $e;
        elseif ($password === $current) $errors['password'] = 'A nova senha deve ser diferente da atual.';
        if ($password !== Request::raw('confirm')) $errors['confirm'] = 'As senhas não conferem.';
        if ($errors) {
            $this->render('auth/change', ['title' => 'Alterar senha', 'forced' => $forced, 'errors' => $errors], 'layouts/auth', 422);
            return;
        }

        Database::run('UPDATE users SET password_hash = ?, must_change_password = 0, reset_token = NULL, reset_token_expiry = NULL, updated_at = NOW() WHERE id = ?', [Password::hash($password), $user['id']]);
        Session::regenerate();
        Audit::log($user, $forced ? 'PASSWORD_CHANGED_FORCED' : 'PASSWORD_CHANGED', 'User', $user['id']);
        $this->flashRedirect('success', 'Senha alterada com sucesso.', '/admin');
    }

    public function forbidden(): void
    {
        $user = Auth::require();
        $this->render('admin/forbidden', ['title' => 'Acesso negado', 'user' => $user], 'layouts/admin', 403);
    }
}
