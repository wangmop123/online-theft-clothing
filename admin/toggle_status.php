<?php
require_once __DIR__ . '/../config.php';
requireAdmin();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $conn = getConnection();
    $id = (int)$_POST['id'];
    
    if ($id == $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'Cannot block your own account']);
        exit();
    }
    
    $stmt = $conn->prepare("SELECT status FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $newStatus = ($user['status'] == 'active') ? 'inactive' : 'active';
    
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $newStatus, $id);
    $success = $stmt->execute();
    
    echo json_encode(['success' => $success, 'new_status' => $newStatus]);
    $conn->close();
}
?>