<?php
// ==================== CART PAGE ====================
// cart.php - Display cart items
include 'config.php';
requireLogin();

$conn = getConnection();
$user_id = $_SESSION['user_id'];

$cart_items = [];
$total = 0;

$sql = "SELECT c.*, p.name, p.price, p.image 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($item = $result->fetch_assoc()) {
    $item['subtotal'] = $item['price'] * $item['quantity'];
    $total += $item['subtotal'];
    $cart_items[] = $item;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cart - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main class="cart-page">
        <div class="container">
            <h1>Shopping Cart</h1>
            
            <?php if (empty($cart_items)): ?>
                <div class="empty-cart">
                    <i class="fas fa-shopping-bag"></i>
                    <h3>Your cart is empty</h3>
                    <p>Looks like you haven't added anything yet</p>
                    <a href="index.php" class="btn-primary">Continue Shopping</a>
                </div>
            <?php else: ?>
                <div class="cart-grid">
                    <div class="cart-items">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item" data-id="<?php echo $item['id']; ?>">
                                <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                                <div class="item-details">
                                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <p>Size: <?php echo $item['size']; ?></p>
                                    <p class="price">रु <?php echo number_format($item['price'], 2); ?></p>
                                </div>
                                <div class="item-quantity">
                                    <button class="qty-minus">-</button>
                                    <input type="number" class="qty-input" value="<?php echo $item['quantity']; ?>" min="1" readonly>
                                    <button class="qty-plus">+</button>
                                </div>
                                <div class="item-subtotal">
                                    रु<?php echo number_format($item['subtotal'], 2); ?>
                                </div>
                                <button class="remove-item" data-id="<?php echo $item['id']; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="cart-summary">
                        <h3>Order Summary</h3>
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span>रु <?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping</span>
                            <span>Calculated at checkout</span>
                        </div>
                        <div class="summary-row total">
                            <span>Total</span>
                            <span>रु <?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="checkout-form">
                            <input type="text" id="shippingAddress" placeholder="Shipping Address" required>
                            <input type="tel" id="phone" placeholder="Phone Number" required>
                            <button id="checkoutBtn" class="btn-primary full-width">Proceed to Checkout</button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include 'footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="script.js"></script>
</body>
</html>