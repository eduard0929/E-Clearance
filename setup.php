<?php
/**
 * HRMU Clearance System - Setup Script
 * Run this once after creating the database
 * Access: http://localhost/E-clearance%20HMRU/setup.php
 * DELETE this file after setup!
 */
require_once __DIR__ . '/config/app.php';

// Only allow local access
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($ip, ['127.0.0.1', '::1', 'localhost']) && php_sapi_name() !== 'cli') {
    die('Setup can only be run locally. Delete this file after use.');
}

$db = getDB();

// Import database schema
$sqlFile = __DIR__ . '/database.sql';
if (!file_exists($sqlFile)) {
    die('database.sql not found');
}

$sql = file_get_contents($sqlFile);
$statements = explode(';', $sql);

echo "<!DOCTYPE html><html><head><title>HRMU Setup</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<style>body{padding:2rem;background:#f0f2f5;}.card{max-width:800px;margin:auto;border-radius:12px;border:none;box-shadow:0 5px 25px rgba(0,0,0,0.1);}</style>";
echo "</head><body><div class='card p-4'>";
echo "<h2 class='mb-4'>HRMU Clearance System Setup</h2>";

$success = true;
$executed = 0;
$errors = [];

foreach ($statements as $stmt) {
    $stmt = trim($stmt);
    if (empty($stmt)) continue;
    
    // Skip USE statements and comments
    if (stripos($stmt, 'USE ') === 0) continue;
    if (stripos($stmt, '--') === 0) continue;
    
    try {
        if ($db->query($stmt) === FALSE) {
            // Ignore "already exists" errors
            if ($db->errno != 1050 && $db->errno != 1061) {
                throw new Exception($db->error . " [SQL: " . substr($stmt, 0, 100) . "...]");
            }
        }
        $executed++;
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}

echo "<div class='alert alert-info'>Database schema processed. Executed $executed statement(s).</div>";

if (count($errors) > 0) {
    echo "<div class='alert alert-warning'>" . count($errors) . " non-critical warnings:</div>";
    echo "<ul class='text-muted'>";
    foreach (array_slice($errors, 0, 5) as $e) {
        echo "<li><small>" . htmlspecialchars($e) . "</small></li>";
    }
    echo "</ul>";
}

// Check if admin exists
$adminCheck = $db->query("SELECT id FROM users WHERE username = 'admin'");
if ($adminCheck && $adminCheck->num_rows > 0) {
    echo "<div class='alert alert-success'>Default admin user already exists.</div>";
} else {
    // Create default admin
    $adminPass = password_hash('admin123', PASSWORD_DEFAULT);
    $adminRole = $db->query("SELECT id FROM roles WHERE role_name = 'admin'")->fetch_assoc()['id'];
    
    if ($adminRole) {
        $stmt = $db->prepare("INSERT INTO users (username, password, first_name, last_name, email, role_id) VALUES (?, ?, ?, ?, ?, ?)");
        $fn = 'System';
        $ln = 'Administrator';
        $em = 'admin@hmru.edu';
        $stmt->bind_param("sssssi", $adminUser, $adminPass, $fn, $ln, $em, $adminRole);
        $adminUser = 'admin';
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Default admin account created!</div>";
        } else {
            echo "<div class='alert alert-danger'>Failed to create admin: " . $db->error . "</div>";
        }
    }
}

// Setup complete
echo "<hr>";
echo "<h5>Setup Complete</h5>";
echo "<ul>";
echo "<li><strong>Admin Login:</strong> username: <code>admin</code> / password: <code>admin123</code></li>";
echo "<li><a href='login-page.php' class='btn btn-primary mt-3'>Go to Login</a></li>";
echo "</ul>";
echo "<div class='alert alert-danger mt-3'><strong>IMPORTANT:</strong> Delete this <code>setup.php</code> file after setup!</div>";
echo "</div></body></html>";
