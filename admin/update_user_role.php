<?php
require_once '../config.php';
requireAdmin();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['role'])) {
    $conn = getConnection();
    $id = (int)$_POST['id'];
    $role = sanitize($_POST['role']);
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $role, $id);
    echo json_encode(['success' => $stmt->execute()]);
    $conn->close();
}
?>