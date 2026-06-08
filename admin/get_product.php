<?php
// ==================== ADMIN - GET PRODUCT ====================
require_once '../config.php';
requireAdmin();
header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $conn = getConnection();
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    
    if ($product) {
        echo json_encode($product);
    } else {
        echo json_encode(['error' => 'Product not found']);
    }
    $conn->close();
} else {
    echo json_encode(['error' => 'No product ID provided']);
}
?>