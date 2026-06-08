<?php
require_once __DIR__ . '/../config.php';
requireAdmin();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $conn = getConnection();
    $id = (int)$_POST['id'];
    
    if ($id == $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'आफैलाई मेटाउन सकिँदैन']);
        exit();
    }
    
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    
    echo json_encode(['success' => $success]);
    $conn->close();
}
?>