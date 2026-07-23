<?php
require_once __DIR__ . '/config/app.php';
requireLogin();

$db = getDB();
$user = getCurrentUser();
$userId = (int)$user['id'];
$role = $user['role_name'];

$emp = null;
$signatoryProfile = null;
if ($role === 'employee') {
    $stmt = $db->prepare("SELECT e.*, d.dept_name, d.dept_code FROM employees e LEFT JOIN departments d ON e.department_id = d.id WHERE e.user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $emp = $stmt->get_result()->fetch_assoc();
}
if ($role === 'signatory') {
    $stmt = $db->prepare("SELECT sp.*, o.office_name, o.office_code, d.dept_name FROM signatory_profiles sp JOIN offices o ON sp.office_id = o.id LEFT JOIN departments d ON o.department_id = d.id WHERE sp.user_id = ? AND sp.is_active = 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $signatoryProfile = $stmt->get_result()->fetch_assoc();
}

if ($role === 'employee' && !$emp) {
    $emp = ['id' => null, 'employee_id' => null, 'position_title' => null, 'employee_type' => null, 'classification' => null, 'contact_number' => null, 'address' => null, 'date_hired' => null, 'dept_name' => null, 'dept_code' => null];
}

$userInitial = strtoupper(substr($user['first_name'],0,1)) . strtoupper(substr($user['last_name'],0,1));
require_once __DIR__ . '/includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - HRMU Clearance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="app-layout">
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
                <li class="nav-item"><a class="nav-link active" href="profile.php"><i class="bi bi-person-circle"></i><span> Profile</span></a></li>
            </ul>
            <?php elseif ($role === 'signatory'): ?>
            <div class="sidebar-divider">Services</div>
            <ul class="sidebar-nav">
                <li class="nav-item"><a class="nav-link" href="signatory/pending.php"><i class="bi bi-inbox"></i><span> Pending</span></a></li>
                <li class="nav-item"><a class="nav-link" href="signatory/monitoring.php"><i class="bi bi-bar-chart-steps"></i><span> Monitoring</span></a></li>
                <li class="nav-item"><a class="nav-link" href="signatory/signature.php"><i class="bi bi-pen"></i><span> My Signature</span></a></li>
                <li class="nav-item"><a class="nav-link active" href="profile.php"><i class="bi bi-person-circle"></i><span> Profile</span></a></li>
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

        <div class="main-content">
            <header class="top-header">
                <div class="d-flex align-items-center gap-2">
                    <button class="sidebar-toggle" onclick="document.getElementById('appSidebar').classList.toggle('show')">
                        <i class="bi bi-list"></i>
                    </button>
                    <div class="page-title">
                        <i class="bi bi-person-circle"></i> Profile
                    </div>
                </div>
                <div class="header-actions">
                    <a class="position-relative text-decoration-none text-dark me-3" href="<?= $notifLink ?>" title="Notifications">
                        <i class="bi bi-bell fs-5"></i>
                        <?php if ($notifCount > 0): ?>
                        <span class="notif-count"><?= $notifCount > 9 ? '9+' : $notifCount ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown user-dropdown">
                        <div class="user-avatar" data-bs-toggle="dropdown" role="button" style="overflow: hidden; padding: 0;">
                            <?= getUserAvatarInner($user) ?>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text fw-bold"><?= htmlspecialchars($_SESSION['full_name']) ?></span></li>
                            <li><span class="dropdown-item-text text-muted small"><?= ucfirst($role) ?></span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>
            </header>

            <div class="page-content animate-fade-in-up">
                <form id="profileForm" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update_profile">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="glass-card-static text-center">
                                <div class="card-body">
                                    <div class="position-relative d-inline-block">
                                        <div id="avatarContainer" class="avatar-circle mx-auto mb-3" style="width:96px;height:96px;font-size:2.2rem;overflow:hidden;padding:0;">
                                            <?= getUserAvatarInner($user) ?>
                                        </div>
                                        <label for="avatarUploadInput" class="btn btn-sm btn-light border position-absolute bottom-0 end-0 rounded-circle p-1 shadow-sm" style="transform: translate(25%, -25%); cursor: pointer;" title="Change Profile Picture">
                                            <i class="bi bi-camera-fill"></i>
                                        </label>
                                        <input type="file" id="avatarUploadInput" name="profile_image" accept="image/*" style="display: none;" onchange="previewAvatar(this)">
                                    </div>
                                    <h5 class="fw-bold"><?= htmlspecialchars($_SESSION['full_name']) ?></h5>
                                    <p class="text-muted mb-1">@<?= htmlspecialchars($user['username']) ?></p>
                                <span class="badge bg-maroon"><?= ucfirst($role) ?></span>
                                <?php if ($role === 'employee' && $emp && $emp['dept_name']): ?>
                                <p class="mt-2 mb-0"><i class="bi bi-building text-maroon"></i> <?= htmlspecialchars($emp['dept_name']) ?></p>
                                <?php endif; ?>
                                <?php if ($role === 'signatory' && $signatoryProfile): ?>
                                <p class="mt-2 mb-0"><i class="bi bi-door-open text-maroon"></i> <?= htmlspecialchars($signatoryProfile['office_name']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="glass-card-static">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-person-vcard text-maroon me-1"></i> Account Information</span>
                                <span class="badge bg-<?= $user['is_active'] ? 'success' : 'danger' ?>">
                                    <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </div>
                            <div class="card-body">
                                    <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">First Name</label>
                                    <input type="text" class="form-control" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Middle Name</label>
                                    <input type="text" class="form-control" name="middle_name" value="<?= htmlspecialchars($user['middle_name'] ?? '') ?>" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" class="form-control" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" readonly>
                                </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" name="email" id="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Gmail Address</label>
                                            <input type="email" class="form-control" name="gmail_address" id="gmail_address" value="<?= htmlspecialchars($user['gmail_address'] ?? '') ?>">
                                        </div>
                                    </div>

                                    <?php if ($role === 'employee' && $emp): ?>
                                    <hr class="section-divider">
                                    <h6 class="fw-bold text-maroon mb-3"><i class="bi bi-briefcase"></i> Employment Details</h6>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Employee ID</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($emp['employee_id'] ?? '') ?>" readonly>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Department</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($emp['dept_name'] ?? 'Unassigned') ?>" readonly>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Position</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($emp['position_title'] ?? '') ?>" readonly>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Employee Type</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars(ucfirst(str_replace('_', ' ', $emp['employee_type'] ?? ''))) ?>" readonly>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Contact Number</label>
                                            <input type="text" class="form-control" name="contact_number" id="contact_number" value="<?= htmlspecialchars($emp['contact_number'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Date Hired</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($emp['date_hired'] ?? '') ?>" readonly>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Address</label>
                                            <textarea class="form-control" name="address" id="address" rows="2"><?= htmlspecialchars($emp['address'] ?? '') ?></textarea>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <?php if ($role === 'signatory' && $signatoryProfile): ?>
                                    <hr class="section-divider">
                                    <h6 class="fw-bold text-maroon mb-3"><i class="bi bi-door-open"></i> Signatory Details</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Office</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($signatoryProfile['office_name']) ?>" readonly>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Department</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($signatoryProfile['dept_name'] ?? 'University-Wide') ?>" readonly>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Designation</label>
                                            <input type="text" class="form-control" name="designation" id="designation" value="<?= htmlspecialchars($signatoryProfile['designation'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <hr class="section-divider">
                                    <h6 class="fw-bold text-maroon mb-3"><i class="bi bi-key"></i> Change Password</h6>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Current Password</label>
                                            <div class="password-wrapper">
                                                <input type="password" class="form-control" name="current_password" id="current_password" placeholder="Leave blank to keep">
                                                <button type="button" class="password-toggle-btn" tabindex="-1"><i class="bi bi-eye"></i></button>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">New Password</label>
                                            <div class="password-wrapper">
                                                <input type="password" class="form-control" name="new_password" id="new_password" placeholder="Leave blank to keep">
                                                <button type="button" class="password-toggle-btn" tabindex="-1"><i class="bi bi-eye"></i></button>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Confirm Password</label>
                                            <div class="password-wrapper">
                                                <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="Leave blank to keep">
                                                <button type="button" class="password-toggle-btn" tabindex="-1"><i class="bi bi-eye"></i></button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <button type="submit" class="btn btn-maroon"><i class="bi bi-check-lg"></i> Save Changes</button>
                                    </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal-backdrop fade" id="sidebarBackdrop" style="display:none;" onclick="document.getElementById('appSidebar').classList.remove('show')"></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
    <script>
    $(document).ready(function() {
        $('#profileForm').on('submit', function(e) {
            e.preventDefault();
            const btn = $(this).find('button[type="submit"]');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');
            const data = new FormData(this);
            $.ajax({
                url: 'ajax/profile_ajax.php',
                type: 'POST',
                data: data,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="bi bi-check-lg"></i> Save Changes');
                    if (res.success) {
                        showToast('success', res.message);
                        if (res.message.includes('password') || res.message.includes('Password')) {
                            $('#current_password, #new_password, #confirm_password').val('');
                        }
                        // Refresh top header avatar if changed
                        if (res.avatar_url) {
                            $('.user-avatar img').attr('src', res.avatar_url);
                        }
                    } else {
                        showToast('danger', res.message);
                    }
                },
                error: function() {
                    btn.prop('disabled', false).html('<i class="bi bi-check-lg"></i> Save Changes');
                    showToast('danger', 'Request failed');
                }
            });
        });
    });

    function previewAvatar(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#avatarContainer').html('<img src="' + e.target.result + '" alt="Avatar" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function showToast(type, message) {
        var bg = type === 'success' ? 'bg-success' : 'bg-danger';
        var toast = '<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999"><div class="toast align-items-center text-white ' + bg + ' border-0 show" role="alert"><div class="d-flex"><div class="toast-body">' + message + '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div></div>';
        $('body').append(toast);
        setTimeout(function() { $(toast).remove(); }, 3000);
    }
    </script>
</body>
</html>