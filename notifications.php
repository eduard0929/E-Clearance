<?php
require_once __DIR__ . '/config/app.php';
requireLogin();

$user = getCurrentUser();
$role = $user['role_name'];
$db = getDB();
$userId = (int)$user['id'];

// Mark all as read if requested
if (isset($_GET['mark_read'])) {
    $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    redirect('notifications.php');
}

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$stmt = $db->prepare("SELECT COUNT(*) as c FROM notifications WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$totalNotif = $stmt->get_result()->fetch_assoc()['c'];
$totalPages = ceil($totalNotif / $perPage);

$stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->bind_param("iii", $userId, $perPage, $offset);
$stmt->execute();
$notifications = $stmt->get_result();

// Mark notifications as read when viewing this page
$stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
$stmt->bind_param("i", $userId);
$stmt->execute();

$userInitial = strtoupper(substr($user['first_name'], 0, 1)) . strtoupper(substr($user['last_name'], 0, 1));
$dir = basename(dirname($_SERVER['PHP_SELF']));
$basePath = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - HRMU Clearance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="app-layout">
        <!-- Sidebar -->
        <aside class="sidebar" id="appSidebar">
            <div class="sidebar-brand">
                <div class="brand-icon"><img src="<?= BASE_URL ?>assets/images/zppsu_logo.png" alt="ZPPsu" class="sidebar-logo"></div>
                <div class="brand-text">HRMU <small>Clearance System</small></div>
            </div>
            <div class="sidebar-divider">Main</div>
            <ul class="sidebar-nav">
                <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2"></i><span> Dashboard</span></a></li>
            </ul>
            <?php if ($role === 'admin'): ?>
            <div class="sidebar-divider">Administration</div>
            <ul class="sidebar-nav">
                <li class="nav-item"><a class="nav-link" href="admin/users.php"><i class="bi bi-people"></i><span> Users</span></a></li>
                <li class="nav-item"><a class="nav-link" href="admin/departments.php"><i class="bi bi-building"></i><span> College Departments</span></a></li>
                <li class="nav-item"><a class="nav-link" href="admin/offices.php"><i class="bi bi-door-open"></i><span> College Offices</span></a></li>
                <li class="nav-item"><a class="nav-link" href="admin/workflow.php"><i class="bi bi-diagram-3"></i><span> Workflow</span></a></li>
                <li class="nav-item"><a class="nav-link" href="admin/clearance_periods.php"><i class="bi bi-calendar-event"></i><span> Periods</span></a></li>
                <li class="nav-item"><a class="nav-link" href="admin/monitoring.php"><i class="bi bi-bar-chart-steps"></i><span> Monitoring</span></a></li>
                <li class="nav-item"><a class="nav-link" href="admin/settings.php"><i class="bi bi-toggles"></i><span> Settings</span></a></li>
                <li class="nav-item"><a class="nav-link" href="admin/audit_logs.php"><i class="bi bi-journal-text"></i><span> Audit Logs</span></a></li>
            </ul>
            <?php elseif ($role === 'employee'): ?>
            <div class="sidebar-divider">Services</div>
            <ul class="sidebar-nav">
                <li class="nav-item"><a class="nav-link" href="employee/clearance.php"><i class="bi bi-file-earmark-text"></i><span> My Clearance</span></a></li>
                <li class="nav-item"><a class="nav-link" href="profile.php"><i class="bi bi-person-circle"></i> Profile</a></li>
            </ul>
            <?php elseif ($role === 'signatory'): ?>
            <div class="sidebar-divider">Services</div>
            <ul class="sidebar-nav">
                <li class="nav-item"><a class="nav-link" href="signatory/pending.php"><i class="bi bi-inbox"></i><span> Pending</span></a></li>
                <li class="nav-item"><a class="nav-link" href="signatory/monitoring.php"><i class="bi bi-bar-chart-steps"></i><span> Monitoring</span></a></li>
                <li class="nav-item"><a class="nav-link" href="signatory/signature.php"><i class="bi bi-pen"></i><span> My Signature</span></a></li>
                <li class="nav-item"><a class="nav-link" href="profile.php"><i class="bi bi-person-circle"></i><span> Profile</span></a></li>
            </ul>
            <?php endif; ?>
            <div class="sidebar-divider">Account</div>
            <ul class="sidebar-nav">
                <li class="nav-item"><a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i><span> Logout</span></a></li>
            </ul>
            <button class="sidebar-collapse-btn" id="sidebarCollapseBtn" title="Toggle sidebar">
                <i class="bi bi-chevron-left collapse-toggle-icon"></i>
            </button>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Header -->
            <header class="top-header">
                <div class="d-flex align-items-center gap-2">
                    <button class="sidebar-toggle" onclick="document.getElementById('appSidebar').classList.toggle('show')">
                        <i class="bi bi-list"></i>
                    </button>
                    <div class="page-title">
                        <i class="bi bi-bell"></i> Notifications
                    </div>
                </div>
                <div class="header-actions">
                    <div class="dropdown user-dropdown">
                        <div class="user-avatar" data-bs-toggle="dropdown" role="button" style="overflow: hidden; padding: 0;">
                            <?= getUserAvatarInner($user) ?>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text fw-bold"><?= htmlspecialchars($_SESSION['full_name']) ?></span></li>
                            <li><span class="dropdown-item-text text-muted small"><?= ucfirst($role) ?></span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person"></i> Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="page-content animate-fade-in-up">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold mb-0"><i class="bi bi-bell text-maroon me-1"></i> Notifications</h4>
                    <div class="d-flex gap-2">
                        <?php if ($totalNotif > 0): ?>
                        <a href="?mark_read=1" class="btn btn-outline-maroon btn-sm" onclick="return confirm('Mark all notifications as read?')">
                            <i class="bi bi-check-all"></i> Mark All Read
                        </a>
                        <?php endif; ?>
                        <a href="dashboard.php" class="btn btn-maroon btn-sm">
                            <i class="bi bi-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>

                <div class="glass-card-static">
                    <div class="card-body p-0">
                        <?php if ($notifications->num_rows > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php while ($notif = $notifications->fetch_assoc()): 
                                $typeClass = '';
                                switch ($notif['type']) {
                                    case 'success':
                                        $typeClass = 'list-group-item-success';
                                        break;
                                    case 'warning':
                                        $typeClass = 'list-group-item-warning';
                                        break;
                                    case 'danger':
                                        $typeClass = 'list-group-item-danger';
                                        break;
                                }
                                $notifHref = '#';
                                if (!empty($notif['link'])) {
                                    $notifHref = $notif['link'];
                                    if (strpos($notifHref, '../') === 0) {
                                        $notifHref = BASE_URL . ltrim(substr($notifHref, 3), '/');
                                    } elseif (!preg_match('#^https?://#i', $notifHref)) {
                                        $notifHref = BASE_URL . ltrim($notifHref, '/');
                                    }
                                }
                            ?>
                            <a href="<?= htmlspecialchars($notifHref) ?>" class="list-group-item list-group-item-action <?= $typeClass ?>">
                                <div class="d-flex w-100 justify-content-between align-items-start">
                                    <div>
                                        <div class="fw-medium"><?= htmlspecialchars($notif['title']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($notif['message'] ?? '') ?></small>
                                    </div>
                                    <small class="text-muted text-nowrap ms-3"><?= timeAgo($notif['created_at']) ?></small>
                                </div>
                            </a>
                            <?php endwhile; ?>
                        </div>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                        <nav class="p-3">
                            <ul class="pagination pagination-sm mb-0 justify-content-center">
                                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                                </li>
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                </li>
                                <?php endfor; ?>
                                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>
                        <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-bell-slash" style="font-size: 3rem; color: var(--grey-300);"></i>
                            <p class="text-muted mt-3 mb-0">No notifications yet.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile sidebar backdrop -->
    <div class="modal-backdrop fade" id="sidebarBackdrop" style="display:none;" onclick="document.getElementById('appSidebar').classList.remove('show')"></div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>