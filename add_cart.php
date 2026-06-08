<?php
// ==================== ADD TO CART ====================
// add_cart.php - Add product to cart
include 'config.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'redirect' => true, 'message' => 'Please login first']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getConnection();
    $user_id = $_SESSION['user_id'];
    $product_id = (int)$_POST['product_id'];
    $size = sanitize($_POST['size']);
    $quantity = (int)$_POST['quantity'];
    
    // Check if product already in cart
    $check = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ? AND size = ?");
    $check->bind_param("iis", $user_id, $product_id, $size);
    $check->execute();
    $result = $check->get_result();
    
    if ($item = $result->fetch_assoc()) {
        $new_qty = $item['quantity'] + $quantity;
        $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $update->bind_param("ii", $new_qty, $item['id']);
        $update->execute();
    } else {
        $insert = $conn->prepare("INSERT INTO cart (user_id, product_id, size, quantity) VALUES (?, ?, ?, ?)");
        $insert->bind_param("iisi", $user_id, $product_id, $size, $quantity);
        $insert->execute();
    }
    
    echo json_encode(['success' => true, 'message' => 'Added to cart!']);
    $conn->close();
    exit();
}
?>