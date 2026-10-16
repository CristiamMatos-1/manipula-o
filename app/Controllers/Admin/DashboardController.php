<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\{Auth, Database, Rbac};
use App\Models\ContentRepo;

final class DashboardController extends AdminController
{
    public function index(): void
    {
        $user = Auth::requirePermission('dashboard:view');
        $isSuper = Rbac::can($user['role'], 'audit:view');
        $posts = ContentRepo::posts();
        $pages = ContentRepo::pages();

        $logs = Database::all(
            'SELECT a.*, u.name AS user_name FROM audit_logs a LEFT JOIN users u ON u.id = a.user_id'
            . ($isSuper ? '' : ' WHERE a.user_id = ?') . ' ORDER BY a.created_at DESC, a.id DESC LIMIT 8',
            $isSuper ? [] : [$user['id']]
        );

        $this->render('admin/dashboard', [
            'title' => 'Dashboard',
            'user' => $user,
            'isSuper' => $isSuper,
            'totalUsers' => (int) Database::value('SELECT COUNT(*) FROM users'),
            'activeUsers' => (int) Database::value('SELECT COUNT(*) FROM users WHERE active = 1'),
            'publishedPosts' => $posts->count('PUBLISHED'),
            'publishedPages' => $pages->count('PUBLISHED'),
            'draftPosts' => $posts->count('DRAFT'),
            'draftPages' => $pages->count('DRAFT'),
            'logs' => $logs,
        ]);
    }
}
