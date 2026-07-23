<?php
require_once __DIR__ . '/config/app.php';
requireLogin();

$user = getCurrentUser();
$role = $user['role_name'];

$db = getDB();
$userId = (int)$user['id'];

$stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->bind_param("i", $userId);
$stmt->execute();
$notifications = $stmt->get_result();

$stmt = $db->prepare("SELECT COUNT(*) as c FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->bind_param("i", $userId);
$stmt->execute();
$unreadCount = $stmt->get_result()->fetch_assoc()['c'];

$userInitial = strtoupper(substr($user['first_name'], 0, 1)) . strtoupper(substr($user['last_name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - HRMU Clearance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="app-layout">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <?php
            $pageTitle = 'Dashboard';
            $pageIcon = 'bi-speedometer2';
            include __DIR__ . '/includes/top_header.php';
            ?>

            <!-- Page Content -->
            <div class="page-content">
                <!-- Welcome -->
                <div class="d-flex justify-content-between align-items-center mb-4 animate-fade-in-up">
                    <div>
                        <h4 class="fw-bold mb-1">Welcome back, <?= htmlspecialchars($user['first_name']) ?>!</h4>
                        <p class="text-muted mb-0">
                            <span class="badge bg-maroon me-1"><?= ucfirst($role) ?></span>
                            <?= date('l, F j, Y') ?>
                        </p>
                    </div>
                </div>

                <?php if ($role === 'admin'): ?>
                <?php
                $totalUsers = $db->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
                $totalClearances = $db->query("SELECT COUNT(*) as c FROM clearance_requests")->fetch_assoc()['c'];
                $pendingClearances = $db->query("SELECT COUNT(*) as c FROM clearance_requests WHERE status = 'pending'")->fetch_assoc()['c'];
                $totalSignatures = $db->query("SELECT COUNT(*) as c FROM clearance_signatures WHERE action = 'signed'")->fetch_assoc()['c'];
                ?>
                <div class="row g-3 mb-4 stagger-children">
                    <div class="col-md-3 col-6">
                        <div class="glass-card stat-card" style="border-left: 4px solid var(--maroon);">
                            <div class="stat-icon" style="background: linear-gradient(135deg, rgba(128,0,0,0.12), rgba(128,0,0,0.05)); color: var(--maroon);">
                                <i class="bi bi-people"></i>
                            </div>
                            <div class="stat-value text-maroon"><?= $totalUsers ?></div>
                            <div class="stat-label">Total Users</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="glass-card stat-card" style="border-left: 4px solid var(--gold);">
                            <div class="stat-icon" style="background: linear-gradient(135deg, rgba(212,175,55,0.2), rgba(212,175,55,0.08)); color: var(--gold-dark);">
                                <i class="bi bi-file-earmark-check"></i>
                            </div>
                            <div class="stat-value text-gold"><?= $totalClearances ?></div>
                            <div class="stat-label">Total Clearances</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="glass-card stat-card" style="border-left: 4px solid #E65100;">
                            <div class="stat-icon" style="background: linear-gradient(135deg, rgba(249,168,37,0.15), rgba(249,168,37,0.05)); color: #E65100;">
                                <i class="bi bi-hourglass-split"></i>
                            </div>
                            <div class="stat-value" style="color:#E65100;"><?= $pendingClearances ?></div>
                            <div class="stat-label">Pending</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="glass-card stat-card" style="border-left: 4px solid var(--success);">
                            <div class="stat-icon" style="background: linear-gradient(135deg, rgba(46,125,50,0.12), rgba(46,125,50,0.05)); color: var(--success);">
                                <i class="bi bi-pen"></i>
                            </div>
                            <div class="stat-value" style="color:var(--success);"><?= $totalSignatures ?></div>
                            <div class="stat-label">Signatures Applied</div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="glass-card-static">
                            <div class="card-header">
                                <i class="bi bi-file-earmark-text text-maroon me-1"></i> Recent Clearance Requests
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table id="recentClearanceTable" class="table table-hover mb-0">
                                        <thead>
                                            <tr><th>Code</th><th>Employee</th><th>Status</th><th>Date</th></tr>
                                        </thead>
                                        <tbody>
                                            <?php $recent = $db->query("SELECT cr.*, u.first_name, u.last_name FROM clearance_requests cr JOIN employees e ON cr.employee_id = e.id JOIN users u ON e.user_id = u.id ORDER BY cr.created_at DESC LIMIT 10"); ?>
                                            <?php while($r = $recent->fetch_assoc()): ?>
                                            <tr>
                                                <td><code><?= htmlspecialchars($r['clearance_code']) ?></code></td>
                                                <td><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></td>
                                                <td><span class="badge bg-<?= $r['status'] === 'completed' ? 'success' : ($r['status'] === 'pending' ? 'warning' : ($r['status'] === 'rejected' ? 'danger' : 'secondary')) ?>"><?= ucfirst($r['status']) ?></span></td>
                                                <td><?= formatDate($r['created_at'], 'M d, Y') ?></td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="glass-card-static">
                            <div class="card-header">
                                <i class="bi bi-journal-text text-maroon me-1"></i> Recent Audit Logs
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table id="recentLogsTable" class="table table-hover mb-0">
                                        <thead>
                                            <tr><th>User</th><th>Action</th><th>Module</th><th>Time</th></tr>
                                        </thead>
                                        <tbody>
                                            <?php $logs = $db->query("SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 10"); ?>
                                            <?php while($log = $logs->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($log['username']) ?></td>
                                                <td><?= htmlspecialchars($log['action_type']) ?></td>
                                                <td><?= htmlspecialchars($log['module']) ?></td>
                                                <td><?= timeAgo($log['created_at']) ?></td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php elseif ($role === 'employee'): ?>
                <?php
                $stmt = $db->prepare("SELECT e.*, d.dept_name FROM employees e LEFT JOIN departments d ON e.department_id = d.id WHERE e.user_id = ?");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $emp = $stmt->get_result()->fetch_assoc();
                $empId = (int)($emp['id'] ?? 0);
                $crStmt = $empId ? $db->prepare("SELECT cr.*, cp.period_name FROM clearance_requests cr JOIN clearance_periods cp ON cr.period_id = cp.id WHERE cr.employee_id = ? ORDER BY cr.created_at DESC LIMIT 1") : null;
                if ($crStmt) { $crStmt->bind_param("i", $empId); $crStmt->execute(); $latestClearance = $crStmt->get_result()->fetch_assoc(); } else { $latestClearance = null; }
                ?>
                <div class="row g-3 mb-4 stagger-children">
                    <div class="col-md-3 col-6">
                        <div class="glass-card stat-card">
                            <div class="stat-icon" style="background: linear-gradient(135deg, rgba(128,0,0,0.12), rgba(128,0,0,0.05)); color: var(--maroon);">
                                <i class="bi bi-person-badge"></i>
                            </div>
                            <div class="stat-label">Department</div>
                            <div class="fw-bold text-maroon"><?= htmlspecialchars($emp['dept_name'] ?? 'Unassigned') ?></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="glass-card stat-card">
                            <div class="stat-icon" style="background: linear-gradient(135deg, rgba(212,175,55,0.2), rgba(212,175,55,0.08)); color: var(--gold-dark);">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div class="stat-label">Clearance Status</div>
                            <div class="mt-1">
                                <?php if ($latestClearance): ?>
                                <span class="badge bg-<?= $latestClearance['status'] === 'completed' ? 'success' : ($latestClearance['status'] === 'in_progress' ? 'warning' : 'secondary') ?> fs-6">
                                    <?= ucfirst(str_replace('_', ' ', $latestClearance['status'])) ?>
                                </span>
                                <?php else: ?>
                                <span class="badge bg-secondary fs-6">No Clearance</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="glass-card stat-card">
                            <div class="stat-icon" style="background: linear-gradient(135deg, rgba(46,125,50,0.12), rgba(46,125,50,0.05)); color: var(--success);">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <div class="stat-label">Signed Steps</div>
                            <div class="fw-bold" style="color:var(--success);">
                                <?= $latestClearance ? ($latestClearance['current_step'] ?? 0) . '/' . ($latestClearance['total_steps'] ?? 0) : '—' ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="glass-card stat-card">
                            <div class="stat-icon" style="background: linear-gradient(135deg, rgba(249,168,37,0.15), rgba(249,168,37,0.05)); color: #E65100;">
                                <i class="bi bi-file-text"></i>
                            </div>
                            <div class="stat-label">Certificate</div>
                            <div class="mt-1">
                                <?php if ($latestClearance && $latestClearance['status'] === 'completed'): ?>
                                <a href="employee/certificate.php?id=<?= $latestClearance['id'] ?>" class="btn btn-sm btn-outline-gold" target="_blank"><i class="bi bi-file-earmark-pdf"></i> View</a>
                                <?php else: ?>
                                <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="glass-card-static">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-file-earmark-text text-maroon me-1"></i> My Clearance Requests</span>
                        <a href="employee/clearance.php?action=new" class="btn btn-maroon btn-sm">
                            <i class="bi bi-plus-lg"></i> New Clearance
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="myClearancesTable" class="table table-hover mb-0">
                                <thead>
                                    <tr><th>Code</th><th>Period</th><th>Progress</th><th>Status</th><th>Actions</th></tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $myClearances = null;
                                    if ($empId) {
                                        $stmt = $db->prepare("SELECT cr.*, cp.period_name FROM clearance_requests cr JOIN clearance_periods cp ON cr.period_id = cp.id WHERE cr.employee_id = ? ORDER BY cr.created_at DESC");
                                        $stmt->bind_param("i", $empId);
                                        $stmt->execute();
                                        $myClearances = $stmt->get_result();
                                    }
                                    ?>
<?php if (!$myClearances || $myClearances->num_rows === 0): ?>
<tr><td class="text-center py-4 text-muted">No clearance requests</td><td></td><td></td><td></td><td></td></tr>
<?php endif; ?>
                                    <?php if ($myClearances): while($c = $myClearances->fetch_assoc()): ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($c['clearance_code']) ?></code></td>
                                        <td><?= htmlspecialchars($c['period_name']) ?></td>
                                        <td>
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar bg-maroon" style="width: <?= $c['total_steps'] > 0 ? ($c['current_step'] / $c['total_steps'] * 100) : 0 ?>%"></div>
                                            </div>
                                            <small class="text-muted"><?= $c['current_step'] ?>/<?= $c['total_steps'] ?> steps</small>
                                        </td>
                                        <td><span class="badge bg-<?= $c['status'] === 'completed' ? 'success' : ($c['status'] === 'in_progress' ? 'warning' : ($c['status'] === 'draft' ? 'secondary' : 'danger')) ?>"><?= ucfirst(str_replace('_', ' ', $c['status'])) ?></span></td>
                                        <td>
                                            <a href="employee/clearance.php?view=<?= $c['id'] ?>" class="btn btn-sm btn-outline-maroon"><i class="bi bi-eye"></i></a>
                                            <?php if ($c['status'] === 'completed' && $c['pdf_path']): ?>
                                            <a href="<?= assetUrl($c['pdf_path']) ?>" class="btn btn-sm btn-outline-gold" target="_blank"><i class="bi bi-download"></i></a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <?php elseif ($role === 'signatory'): ?>
                <?php
                $stmt = $db->prepare("SELECT sp.*, o.office_name FROM signatory_profiles sp JOIN offices o ON sp.office_id = o.id WHERE sp.user_id = ? AND sp.is_active = 1");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $signatoryProfile = $stmt->get_result()->fetch_assoc();
                $pendingCount = 0;
                $signedCount = 0;
                if ($signatoryProfile) {
                    $stmt = $db->prepare("SELECT COUNT(*) as c FROM clearance_signatures cs JOIN clearance_requests cr ON cs.clearance_id = cr.id WHERE cs.signatory_id = ? AND cs.action = 'pending'");
                    $stmt->bind_param("i", $userId);
                    $stmt->execute();
                    $pendingCount = $stmt->get_result()->fetch_assoc()['c'];
                    $stmt = $db->prepare("SELECT COUNT(*) as c FROM clearance_signatures cs JOIN clearance_requests cr ON cs.clearance_id = cr.id WHERE cs.signatory_id = ? AND cs.action = 'signed'");
                    $stmt->bind_param("i", $userId);
                    $stmt->execute();
                    $signedCount = $stmt->get_result()->fetch_assoc()['c'];
                }
                ?>
                <div class="row g-3 mb-4 stagger-children">
                    <div class="col-md-4 col-6">
                        <div class="glass-card stat-card">
                            <div class="stat-icon" style="background: linear-gradient(135deg, rgba(128,0,0,0.12), rgba(128,0,0,0.05)); color: var(--maroon);">
                                <i class="bi bi-building"></i>
                            </div>
                            <div class="stat-label">Your Office</div>
                            <div class="fw-bold text-maroon"><?= htmlspecialchars($signatoryProfile['office_name'] ?? 'N/A') ?></div>
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="glass-card stat-card">
                            <div class="stat-icon" style="background: linear-gradient(135deg, rgba(249,168,37,0.15), rgba(249,168,37,0.05)); color: #E65100;">
                                <i class="bi bi-inbox"></i>
                            </div>
                            <div class="stat-value" style="color:#E65100;"><?= $pendingCount ?></div>
                            <div class="stat-label">Pending Approvals</div>
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="glass-card stat-card">
                            <div class="stat-icon" style="background: linear-gradient(135deg, rgba(46,125,50,0.12), rgba(46,125,50,0.05)); color: var(--success);">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <div class="stat-value" style="color:var(--success);"><?= $signedCount ?></div>
                            <div class="stat-label">Signed</div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-8">
                        <div class="glass-card-static">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-inbox text-maroon me-1"></i> Pending Clearance Requests</span>
                                <a href="signatory/pending.php" class="btn btn-maroon btn-sm">View All</a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table id="pendingApprovalsTable" class="table table-hover mb-0">
                                        <thead>
                                            <tr><th>Code</th><th>Employee</th><th>Step</th><th>Actions</th></tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sigCount = 0;
                                            if ($signatoryProfile) {
                                                $stmt = $db->prepare("
                                                    SELECT cr.*, u.first_name, u.last_name, ws.step_order 
                                                    FROM clearance_signatures cs 
                                                    JOIN clearance_requests cr ON cs.clearance_id = cr.id 
                                                    JOIN employees e ON cr.employee_id = e.id 
                                                    JOIN users u ON e.user_id = u.id 
                                                    JOIN workflow_steps ws ON cs.step_id = ws.id 
                                                    WHERE cs.signatory_id = ? AND cs.action = 'pending' 
                                                    ORDER BY cs.created_at DESC LIMIT 10");
                                                $stmt->bind_param("i", $userId);
                                                $stmt->execute();
                                                $pending = $stmt->get_result();
                                                while($p = $pending->fetch_assoc()): $sigCount++;
                                            ?>
                                            <tr>
                                                <td><code><?= htmlspecialchars($p['clearance_code']) ?></code></td>
                                                <td><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?></td>
                                                <td>Step <?= $p['step_order'] ?></td>
                                                <td>
                                                    <a href="signatory/pending.php?view=<?= $p['id'] ?>" class="btn btn-sm btn-outline-maroon"><i class="bi bi-eye"></i></a>
                                                    <a href="signatory/sign.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-gold"><i class="bi bi-pen"></i> Sign</a>
                                                </td>
                                            </tr>
                                            <?php endwhile; } ?>
<?php if ($sigCount === 0): ?>
<tr><td class="text-center py-4 text-muted">No pending approvals</td><td></td><td></td><td></td></tr>
<?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="glass-card-static">
                            <div class="card-header"><i class="bi bi-pen text-maroon me-1"></i> My Signature</div>
                            <div class="card-body text-center">
                                <?php if ($signatoryProfile && $signatoryProfile['signature_image']): ?>
                                <img src="<?= assetUrl($signatoryProfile['signature_image']) ?>" alt="Signature" style="max-height: 100px;" class="mb-3">
                                <p class="text-success"><i class="bi bi-check-circle"></i> Signature saved</p>
                                <a href="signatory/signature.php" class="btn btn-sm btn-outline-maroon"><i class="bi bi-pen"></i> Update</a>
                                <?php else: ?>
                                <div class="empty-state">
                                    <i class="bi bi-pen"></i>
                                    <p>No signature yet. Create your digital signature to start signing.</p>
                                    <a href="signatory/signature.php" class="btn btn-gold btn-sm"><i class="bi bi-pen"></i> Create Signature</a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Mobile sidebar backdrop -->
    <div class="modal-backdrop fade" id="sidebarBackdrop" style="display:none;" onclick="document.getElementById('appSidebar').classList.remove('show')"></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="assets/js/app.js"></script>
    <script>
    $(document).ready(function() {
        if ($('#recentClearanceTable').length) {
            $('#recentClearanceTable').DataTable({ pageLength: 10, lengthChange: false, columnDefs: [{ targets: [3], orderable: false }] });
        }
        if ($('#recentLogsTable').length) {
            $('#recentLogsTable').DataTable({ pageLength: 10, lengthChange: false, ordering: false });
        }
        if ($('#myClearancesTable').length) {
            $('#myClearancesTable').DataTable({ pageLength: 10, lengthChange: false, columnDefs: [{ targets: [4], orderable: false }] });
        }
        if ($('#pendingApprovalsTable').length) {
            $('#pendingApprovalsTable').DataTable({ pageLength: 10, lengthChange: false, columnDefs: [{ targets: [3], orderable: false }] });
        }
    });
    </script>
</body>
</html>