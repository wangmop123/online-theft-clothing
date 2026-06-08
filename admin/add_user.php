<?php
require_once __DIR__ . '/../config.php';
requireAdmin();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = sanitize($_POST['role']);
    $status = 'active';
    
    $conn = getConnection();
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $full_name, $email, $password, $role, $status);
    $success = $stmt->execute();
    
    echo json_encode(['success' => $success, 'id' => $conn->insert_id]);
    $conn->close();
}
?>