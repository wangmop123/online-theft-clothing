<?php
require_once __DIR__ . '/../config.php';
requireAdmin();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $role = sanitize($_POST['role']);
    $status = sanitize($_POST['status']);
    
    $conn = getConnection();
    $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, role=?, status=? WHERE id=?");
    $stmt->bind_param("ssssi", $full_name, $email, $role, $status, $id);
    $success = $stmt->execute();
    
    // in case of password update
    if (!empty($_POST['password'])) {
        $pwd = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt2 = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt2->bind_param("si", $pwd, $id);
        $stmt2->execute();
    }
    
    echo json_encode(['success' => $success]);
    $conn->close();
}
?>