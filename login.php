<?php
require_once __DIR__ . '/config/app.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Username and password are required']);
        exit;
    }

    $db = getDB();
    $stmt = $db->prepare("SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.username = ? AND u.is_active = 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role_name'];
        $mid = $user['middle_name'] ?? '';
        $midInit = $mid ? ' ' . strtoupper(substr($mid, 0, 1)) . '.' : ' ';
        $_SESSION['full_name'] = $user['first_name'] . $midInit . $user['last_name'];

        $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->bind_param("i", $user['id']);
        $stmt->execute();

        logAudit('login', 'auth', "User {$user['username']} logged in");

        $redirect = BASE_URL . 'dashboard.php';
        echo json_encode(['success' => true, 'message' => 'Login successful', 'redirect' => $redirect]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
    }
}
