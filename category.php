<?php
// ==================== CATEGORY PAGE ====================
include 'config.php';

$category = isset($_GET['cat']) ? sanitize($_GET['cat']) : 'men';
$valid_categories = ['men', 'women', 'kids'];

if (!in_array($category, $valid_categories)) {
    $category = 'men';
}

$conn = getConnection();
$stmt = $conn->prepare("SELECT * FROM products WHERE category = ? ORDER BY created_at DESC");
$stmt->bind_param("s", $category);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo ucfirst($category); ?> Collection - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main>
        <section class="category-hero" style="background: linear-gradient(rgba(10,25,47,0.8), rgba(10,25,47,0.9)), url('assets/images/<?php echo $category; ?>-banner.jpg'); background-size: cover; background-position: center; padding: 6rem 2rem; text-align: center; color: white;">
            <div class="container">
                <h1><?php echo ucfirst($category); ?>'s Collection</h1>
                <p>Discover the latest trends in <?php echo $category; ?>'s fashion</p>
            </div>
        </section>
        
        <section class="products-section">
            <div class="container">
                <div class="filter-bar">
                    <select id="sortFilter">
                        <option value="newest">Newest First</option>
                        <option value="price_low">Price: Low to High</option>
                        <option value="price_high">Price: High to Low</option>
                    </select>
                </div>
                <div class="products-grid" id="productsContainer">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card" data-id="<?php echo $product['id']; ?>">
                            <div class="product-image">
                                <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" onerror="this.src='assets/images/placeholder.jpg'">
                                <?php if ($product['old_price'] > 0): ?>
                                    <span class="discount-badge">-<?php echo round((($product['old_price'] - $product['price']) / $product['old_price']) * 100); ?>%</span>
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                <div class="price">
                                    <span class="current">रु <?php echo number_format($product['price'], 2); ?></span>
                                    <?php if ($product['old_price'] > 0): ?>
                                        <span class="old">रु <?php echo number_format($product['old_price'], 2); ?></span>
                                    <?php endif; ?>
                                </div>
                                <button class="add-to-cart"
                                    data-id="<?php echo $product['id']; ?>"
                                    data-name="<?php echo $product['name']; ?>"
                                    data-image="<?php echo $product['image']; ?>">
                                    <i class="fas fa-shopping-bag"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </main>
    
    <!-- ==================== IMPROVED ADD TO CART MODAL ==================== -->
    <div id="cartModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <div style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
                <div class="modal-product-image">
                    <img id="modalImage" style="width: 180px; object-fit: cover; border-radius: 12px;">
                </div>
                <div class="modal-product-info">
                    <h3 id="modalName" style="margin-bottom: 0.5rem;"></h3>
                    <div class="modal-product-price">
                        <span id="modalCurrentPrice" class="current-price"></span>
                        <span id="modalOldPrice" class="old-price"></span>
                    </div>
                    <div class="modal-product-size">
                        <label>Size:</label>
                        <select id="modalSize">
                            <option>S</option><option>M</option><option>L</option><option>XL</option>
                        </select>
                    </div>
                    <div class="modal-product-quantity">
                        <label>Quantity:</label>
                        <input type="number" id="modalQty" value="1" min="1">
                    </div>
                    <div class="modal-buttons" style="margin-top: 1rem;">
                        <button id="confirmAdd" class="btn-primary">Add to Cart</button>
                        <button id="buyNowBtn" class="btn-secondary">Buy Now</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    
    <div id="toast" class="toast"></div>
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
    <script src="script.js"></script>

    <script>
        // ==================== MODAL LOGIC (ADD TO CART & BUY NOW) ====================
        $(document).ready(function() {
            // Open modal when Add to Cart is clicked
            $(document).on('click', '.add-to-cart', function(e) {
                e.preventDefault();
                const btn = $(this);
                const productId = btn.data('id');
                const productName = btn.data('name');
                const productImage = btn.data('image');
                const productPrice = btn.closest('.product-card').find('.price .current').text();
                const productOldPrice = btn.closest('.product-card').find('.price .old').text();

                $('#modalImage').attr('src', productImage);
                $('#modalName').text(productName);
                $('#modalCurrentPrice').text(productPrice);
                $('#modalOldPrice').text(productOldPrice);
                $('#modalSize').val('M');
                $('#modalQty').val(1);
                $('#confirmAdd').data('id', productId);
                $('#buyNowBtn').data('id', productId);
                $('#cartModal').addClass('show');
            });

            // Close modal
            $('.close-modal, #cartModal').on('click', function(e) {
                if (e.target === this || $(e.target).hasClass('close-modal')) {
                    $('#cartModal').removeClass('show');
                }
            });

            // Add to Cart from modal
            $('#confirmAdd').on('click', function() {
                const productId = $(this).data('id');
                const size = $('#modalSize').val();
                const quantity = $('#modalQty').val();
                addToCart(productId, size, quantity, false);
            });

            // Buy Now from modal
            $('#buyNowBtn').on('click', function() {
                const productId = $(this).data('id');
                const size = $('#modalSize').val();
                const quantity = $('#modalQty').val();
                addToCart(productId, size, quantity, true);
            });

            function addToCart(productId, size, quantity, redirectToCheckout = false) {
                $.ajax({
                    url: 'add_cart.php',
                    method: 'POST',
                    data: { product_id: productId, size: size, quantity: quantity },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showToast('Added to cart!');
                            loadCartCount();
                            $('#cartModal').removeClass('show');
                            if (redirectToCheckout) {
                                window.location.href = 'cart.php';
                            }
                        } else if (response.redirect) {
                            window.location.href = 'login.php';
                        } else {
                            showToast(response.message || 'Error adding to cart', 'error');
                        }
                    },
                    error: function() {
                        showToast('Server error', 'error');
                    }
                });
            }

            // Helper functions (if not already in script.js)
            function showToast(message, type = 'success') {
                const toast = $('#toast');
                toast.removeClass('success error');
                toast.addClass(type);
                toast.text(message);
                toast.addClass('show');
                setTimeout(() => toast.removeClass('show'), 3000);
            }
            function loadCartCount() {
                $.ajax({
                    url: 'get_cart_count.php',
                    method: 'GET',
                    success: function(response) {
                        if (response.count !== undefined) $('#cartCount').text(response.count);
                    }
                });
            }
        });
    </script>
</body>
</html>