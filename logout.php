<?php
require_once __DIR__ . '/config/app.php';
if (isLoggedIn()) {
    logAudit('logout', 'auth', 'User logged out');
}
session_destroy();
redirect('login-page.php');
