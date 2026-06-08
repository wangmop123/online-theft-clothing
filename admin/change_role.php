<?php
require_once __DIR__ . '/../config.php';
requireAdmin();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $conn = getConnection();
    $id = (int)$_POST['id'];
    
    if ($id == $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'आफ्नै भूमिका परिवर्तन गर्न सकिँदैन']);
        exit();
    }
    
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $newRole = ($user['role'] == 'admin') ? 'user' : 'admin';
    
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $newRole, $id);
    $success = $stmt->execute();
    
    echo json_encode(['success' => $success, 'new_role' => $newRole]);
    $conn->close();
}
?>