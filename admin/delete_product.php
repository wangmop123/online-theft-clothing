<?php
// ==================== ADMIN - DELETE PRODUCT ====================
// admin/delete_product.php - Remove product (with existence check)
require_once '../config.php';
requireAdmin();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $conn = getConnection();
    $id = (int)$_POST['id'];
    
    // Check if product exists
    $check = $conn->prepare("SELECT id FROM products WHERE id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        $conn->close();
        exit();
    }
    
    // Optional: Check if product is in any order (to avoid deleting referenced products)
    $orderCheck = $conn->prepare("SELECT id FROM order_items WHERE product_id = ? LIMIT 1");
    $orderCheck->bind_param("i", $id);
    $orderCheck->execute();
    if ($orderCheck->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete: Product has existing orders']);
        $conn->close();
        exit();
    }
    
    // Delete product
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    
    echo json_encode(['success' => $success]);
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>