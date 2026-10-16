<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\{Auth, Database, Request};

final class AuditController extends AdminController
{
    private const PAGE_SIZE = 25;

    public function index(): void
    {
        $user = Auth::requirePermission('audit:view');
        $page = max(1, (int) Request::query('page'));
        $q = Request::query('q');

        $where = '';
        $params = [];
        if ($q !== '') {
            $like = '%' . addcslashes($q, '%_\\') . '%';
            $where = 'WHERE a.user_email LIKE ? OR a.action LIKE ? OR a.entity LIKE ? OR a.details LIKE ?';
            $params = [$like, $like, $like, $like];
        }
        $total = (int) Database::value("SELECT COUNT(*) FROM audit_logs a $where", $params);
        $logs = Database::all(
            "SELECT a.*, u.name AS user_name FROM audit_logs a LEFT JOIN users u ON u.id = a.user_id $where ORDER BY a.created_at DESC, a.id DESC LIMIT " . self::PAGE_SIZE . ' OFFSET ' . (($page - 1) * self::PAGE_SIZE),
            $params
        );
        $this->render('admin/audit', [
            'title' => 'Auditoria', 'user' => $user, 'logs' => $logs, 'q' => $q, 'page' => $page, 'total' => $total,
            'pages' => max(1, (int) ceil($total / self::PAGE_SIZE)),
        ]);
    }
}
