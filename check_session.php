<?php
// ==================== CHECK SESSION ====================
// check_session.php - Verify user session via AJAX
include 'config.php';
header('Content-Type: application/json');

echo json_encode([
    'logged_in' => isLoggedIn(),
    'is_admin' => isAdmin(),
    'user_name' => $_SESSION['user_name'] ?? null
]);
?>