<?php
require_once __DIR__ . '/config/app.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRMU Clearance System - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="login-page">
    <a href="index.php" class="back-to-home-link" style="position:absolute;top:1.5rem;left:1.5rem;color:rgba(255,255,255,0.85);font-size:0.95rem;text-decoration:none;z-index:10;display:inline-flex;align-items:center;gap:0.4rem;padding:0.5rem 1rem;border-radius:8px;background:rgba(0,0,0,0.25);backdrop-filter:blur(6px);transition:all 0.3s ease;">
        <i class="bi bi-arrow-left"></i> Back to Home
    </a>

    <div class="login-container">
        <div class="login-brand">
            <div class="brand-icon">
                <img src="<?= assetUrl('assets/images/zppsu_logo.png') ?>" alt="HRMU" style="width:64px;height:64px;">
            </div>
            <h1>HRMU Clearance</h1>
            <p>Employee Clearance Management System</p>
        </div>

        <div class="login-card">
            <div class="card-title">
                <i class="bi bi-person-badge me-1"></i> Sign into your account
            </div>
            <div id="alertMessage"></div>
            <form id="loginForm">
                <div class="login-field mb-3">
                    <label for="loginUsername" class="login-label"><i class="bi bi-person me-1"></i> Username</label>
                    <input type="text" name="username" class="form-control" id="loginUsername" placeholder="Enter your username" required autocomplete="username">
                </div>
                <div class="login-field mb-4">
                    <label for="loginPassword" class="login-label"><i class="bi bi-lock me-1"></i> Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" class="form-control" id="loginPassword" placeholder="Enter your password" required autocomplete="current-password">
                        <button type="button" class="password-toggle-btn" tabindex="-1"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
                <button type="submit" class="login-btn" id="loginBtn">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
                </button>
            </form>
        </div>

        <div class="login-footer">
            <p>&copy; <?= date('Y') ?> ZPPSU HRMU. All rights reserved.</p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
    <script>
    $('#loginForm').submit(function(e) {
        e.preventDefault();
        const btn = $(this).find('button[type="submit"]');
        const icon = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Signing in...');
        $.ajax({
            url: 'login.php',
            method: 'POST',
            dataType: 'json',
            data: $(this).serialize(),
            success: function(res) {
                if (res.success) {
                    $('#alertMessage').html('<div class="alert alert-success mb-3"><i class="bi bi-check-circle me-1"></i> Login successful. Redirecting...</div>');
                    setTimeout(function() { window.location.href = res.redirect || '<?= BASE_URL ?>dashboard.php'; }, 800);
                } else {
                    $('#alertMessage').html('<div class="alert mb-3"><i class="bi bi-exclamation-triangle me-1"></i> ' + res.message + '</div>');
                    btn.prop('disabled', false).html(icon);
                }
            },
            error: function() {
                $('#alertMessage').html('<div class="alert mb-3"><i class="bi bi-exclamation-triangle me-1"></i> Connection error</div>');
                btn.prop('disabled', false).html(icon);
            }
        });
    });
    </script>
</body>
</html>
