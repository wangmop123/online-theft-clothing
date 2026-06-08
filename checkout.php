<?php
// ==================== CHECKOUT HANDLER ====================
// checkout.php - Process order
include 'config.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getConnection();
    $user_id = $_SESSION['user_id'];
    $address = sanitize($_POST['address']);
    $phone = sanitize($_POST['phone']);
    
    // Get cart items
    $cart_sql = "SELECT c.*, p.name, p.price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?";
    $stmt = $conn->prepare($cart_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    if (empty($cart_items)) {
        echo json_encode(['success' => false, 'message' => 'Cart is empty']);
        exit();
    }
    
    // Calculate total
    $total = 0;
    foreach ($cart_items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    
    // Generate order number
    $order_number = 'OTC-' . strtoupper(uniqid());
    
    // Create order
    $conn->begin_transaction();
    
    try {
        $order_sql = "INSERT INTO orders (user_id, order_number, total_amount, status, shipping_address, phone) VALUES (?, ?, ?, 'pending', ?, ?)";
        $stmt = $conn->prepare($order_sql);
        $stmt->bind_param("isdss", $user_id, $order_number, $total, $address, $phone);
        $stmt->execute();
        $order_id = $conn->insert_id;
        
        // Add order items
        $item_sql = "INSERT INTO order_items (order_id, product_id, product_name, quantity, size, price) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($item_sql);
        
        foreach ($cart_items as $item) {
            $stmt->bind_param("iisisd", $order_id, $item['product_id'], $item['name'], $item['quantity'], $item['size'], $item['price']);
            $stmt->execute();
            
            // Update stock
            $update_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $update_stock->bind_param("ii", $item['quantity'], $item['product_id']);
            $update_stock->execute();
        }
        
        // Clear cart
        $clear_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $clear_cart->bind_param("i", $user_id);
        $clear_cart->execute();
        
        $conn->commit();
        echo json_encode(['success' => true, 'order_number' => $order_number]);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Order failed: ' . $e->getMessage()]);
    }
    
    $conn->close();
    exit();
}
?>