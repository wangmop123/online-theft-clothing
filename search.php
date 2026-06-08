<?php
// ==================== SEARCH PAGE ====================
// search.php - Product search results
include 'config.php';

$query = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$conn = getConnection();

if ($query) {
    $search = "%$query%";
    $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ? OR description LIKE ?");
    $stmt->bind_param("ss", $search, $search);
    $stmt->execute();
    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $products = [];
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search: <?php echo htmlspecialchars($query); ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main style="padding: 8rem 2rem 4rem;">
        <div class="container">
            <h1>Search Results for "<?php echo htmlspecialchars($query); ?>"</h1>
            <p><?php echo count($products); ?> products found</p>
            
            <div class="products-grid" style="margin-top: 2rem;">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                            <?php if ($product['old_price'] > 0): ?>
                                <span class="discount-badge">-<?php echo round((($product['old_price'] - $product['price']) / $product['old_price']) * 100); ?>%</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <div class="price">
                                <span class="current">$<?php echo number_format($product['price'], 2); ?></span>
                                <?php if ($product['old_price'] > 0): ?>
                                    <span class="old">$<?php echo number_format($product['old_price'], 2); ?></span>
                                <?php endif; ?>
                            </div>
                            <button class="add-to-cart" data-id="<?php echo $product['id']; ?>"><i class="fas fa-shopping-bag"></i> Add to Cart</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (empty($products)): ?>
                <div style="text-align: center; padding: 4rem;">
                    <i class="fas fa-search" style="font-size: 3rem; color: var(--gray);"></i>
                    <h3>No products found</h3>
                    <p>Try searching with different keywords</p>
                    <a href="index.php" class="btn-primary">Continue Shopping</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include 'footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="script.js"></script>
</body>
</html>