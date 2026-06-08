<?php
require_once __DIR__ . '/../config.php';
requireAdmin();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT id, full_name, email, role, status FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    echo json_encode($user);
    $conn->close();
}
?>