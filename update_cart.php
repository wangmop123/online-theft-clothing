<?php
// ==================== UPDATE CART ====================
// update_cart.php - Update cart item quantity
include 'config.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getConnection();
    $cart_id = (int)$_POST['cart_id'];
    $quantity = (int)$_POST['quantity'];
    
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("iii", $quantity, $cart_id, $_SESSION['user_id']);
    $success = $stmt->execute();
    
    echo json_encode(['success' => $success]);
    $conn->close();
}
?>