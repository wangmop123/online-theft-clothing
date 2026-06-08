<?php
// ==================== REMOVE FROM CART ====================
// remove_cart.php - Delete cart item
include 'config.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getConnection();
    $cart_id = (int)$_POST['cart_id'];
    
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cart_id, $_SESSION['user_id']);
    $success = $stmt->execute();
    
    echo json_encode(['success' => $success]);
    $conn->close();
}
?>