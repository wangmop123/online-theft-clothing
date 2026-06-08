<?php
// ==================== ORDERS PAGE ====================
// orders.php - View user orders
include 'config.php';
requireLogin();

$conn = getConnection();
$user_id = $_SESSION['user_id'];

$orders_sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY ordered_at DESC";
$stmt = $conn->prepare($orders_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main style="padding: 8rem 2rem 4rem;">
        <div class="container">
            <h1>My Orders</h1>
            
            <?php if (empty($orders)): ?>
                <div class="empty-orders" style="text-align: center; padding: 4rem;">
                    <i class="fas fa-box-open" style="font-size: 4rem; color: var(--gray);"></i>
                    <h3>No orders yet</h3>
                    <p>Start shopping to see your orders here</p>
                    <a href="index.php" class="btn-primary">Shop Now</a>
                </div>
            <?php else: ?>
                <div class="orders-list">
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card" style="background: var(--white); border-radius: 15px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: var(--shadow);">
                            <div style="display: flex; justify-content: space-between; flex-wrap: wrap; margin-bottom: 1rem;">
                                <div>
                                    <strong>Order #:</strong> <?php echo $order['order_number']; ?>
                                </div>
                                <div>
                                    <strong>Date:</strong> <?php echo date('M d, Y', strtotime($order['ordered_at'])); ?>
                                </div>
                                <div>
                                    <strong>Total:</strong> $<?php echo number_format($order['total_amount'], 2); ?>
                                </div>
                                <div>
                                    <span class="status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span>
                                </div>
                            </div>
                            <div>
                                <strong>Shipping Address:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?>
                            </div>
                            <div>
                                <strong>Phone:</strong> <?php echo $order['phone']; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include 'footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="script.js"></script>
</body>
</html>