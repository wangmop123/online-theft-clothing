<?php
include 'config.php';  // first include config

// Then check admin redirect
if (isLoggedIn() && isAdmin()) {
    header('Location: admin/dashboard.php');
    exit();
}

// Get featured products
$conn = getConnection();
$stmt = $conn->prepare("SELECT * FROM products WHERE featured = 1 LIMIT 8");
$stmt->execute();
$featured_products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Premium Streetwear</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="nav-container">
                <div class="logo">
                    <a href="index.php">OTC<span class="logo-accent">THEFT</span></a>
                </div>
                <div class="nav-links">
                    <a href="index.php" class="active">Home</a>
                    <a href="category.php?cat=men">Men</a>
                    <a href="category.php?cat=women">Women</a>
                    <a href="category.php?cat=kids">Kids</a>
                    <a href="about.php">About</a>
                </div>
                <div class="nav-icons">
                    <button class="search-btn" id="searchBtn"><i class="fas fa-search"></i></button>
                    <a href="cart.php" class="cart-icon">
                        <i class="fas fa-shopping-bag"></i>
                        <span class="cart-count" id="cartCount">0</span>
                    </a>
                    <?php if (isLoggedIn()): ?>
                        <div class="user-dropdown">
                            <button class="user-btn"><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?> <i class="fas fa-chevron-down"></i></button>
                            <div class="dropdown-menu">
                                <?php if (isAdmin()): ?>
                                    <a href="admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                                <?php endif; ?>
                                <a href="orders.php"><i class="fas fa-box"></i> My Orders</a>
                                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="login-link"><i class="fas fa-user"></i> Login</a>
                    <?php endif; ?>
                </div>
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
            </div>
            <div class="search-bar" id="searchBar">
                <input type="text" id="searchInput" placeholder="Search for products...">
                <button id="searchSubmit"><i class="fas fa-search"></i></button>
            </div>
        </nav>
    </header>

    <main>
        <!-- ==================== 5‑IMAGE CAROUSEL ==================== -->
        <div class="banner-carousel">
            <div class="banner-slides">
                <img src="assets/images/banner1.jpg" alt="Banner 1">
                <img src="assets/images/banner2.jpg" alt="Banner 2">
                <img src="assets/images/banner3.jpg" alt="Banner 3">
                <img src="assets/images/banner4.jpg" alt="Banner 4">
                <img src="assets/images/banner5.jpg" alt="Banner 5">
            </div>
            <div class="banner-hero-content">
                <h1>STEAL THE STYLE</h1>
                <p>Discover premium thrift fashion at unbeatable prices.</p>
                <a href="category.php?cat=men" class="banner-btn-shop">SHOP NOW</a>
            </div>
            <button class="banner-btn prev">❮</button>
            <button class="banner-btn next">❯</button>
            <div class="banner-dots"></div>
        </div>

        <script>
            // Pure carousel script – no extra AJAX handlers
            (function() {
                const slides = document.querySelectorAll('.banner-slides img');
                if (slides.length === 0) return;
                let current = 0;
                const total = slides.length;
                const dotsContainer = document.querySelector('.banner-dots');
                
                dotsContainer.innerHTML = '';
                for (let i = 0; i < total; i++) {
                    const dot = document.createElement('span');
                    dot.classList.add('dot');
                    dot.dataset.index = i;
                    dot.addEventListener('click', () => goToSlide(i));
                    dotsContainer.appendChild(dot);
                }
                const dots = document.querySelectorAll('.dot');
                
                function updateCarousel() {
                    const offset = -current * 100;
                    document.querySelector('.banner-slides').style.transform = `translateX(${offset}%)`;
                    dots.forEach((dot, idx) => dot.classList.toggle('active', idx === current));
                }
                
                function goToSlide(index) {
                    current = (index + total) % total;
                    updateCarousel();
                }
                
                const nextBtn = document.querySelector('.next');
                const prevBtn = document.querySelector('.prev');
                if (nextBtn) nextBtn.addEventListener('click', () => goToSlide(current + 1));
                if (prevBtn) prevBtn.addEventListener('click', () => goToSlide(current - 1));
                updateCarousel();
                
                let auto = setInterval(() => goToSlide(current + 1), 5000);
                const container = document.querySelector('.banner-carousel');
                container.addEventListener('mouseenter', () => clearInterval(auto));
                container.addEventListener('mouseleave', () => auto = setInterval(() => goToSlide(current + 1), 5000));
            })();
        </script>

        <section class="featured">
            <div class="container">
                <div class="section-header">
                    <h2>Trending <span class="accent">Now</span></h2>
                    <p>Most wanted pieces this season</p>
                </div>
                <div class="products-grid" id="featuredProducts">
                    <?php foreach ($featured_products as $product): ?>
                        <div class="product-card" data-id="<?php echo $product['id']; ?>">
                            <div class="product-image">
                                <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" onerror="this.src='assets/images/placeholder.jpg'">
                                <?php if ($product['old_price'] > 0): ?>
                                    <span class="discount-badge">-<?php echo round((($product['old_price'] - $product['price']) / $product['old_price']) * 100); ?>%</span>
                                <?php endif; ?>
                                <button class="quick-view" data-id="<?php echo $product['id']; ?>"><i class="fas fa-eye"></i></button>
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
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="categories">
            <div class="container">
                <div class="section-header">
                    <h2>Shop By <span class="accent">Category</span></h2>
                    <p>Explore our curated collections</p>
                </div>
                <div class="categories-grid">
                    <a href="category.php?cat=men" class="category-card men-cat">
                        <div class="category-overlay"></div>
                        <h3>Men</h3>
                        <span>Shop Now <i class="fas fa-arrow-right"></i></span>
                    </a>
                    <a href="category.php?cat=women" class="category-card women-cat">
                        <div class="category-overlay"></div>
                        <h3>Women</h3>
                        <span>Shop Now <i class="fas fa-arrow-right"></i></span>
                    </a>
                    <a href="category.php?cat=kids" class="category-card kids-cat">
                        <div class="category-overlay"></div>
                        <h3>Kids</h3>
                        <span>Shop Now <i class="fas fa-arrow-right"></i></span>
                    </a>
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

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>Online Theft <span>Clothing</span></h3>
                <p>Premium streetwear for the bold and fearless. Redefining urban fashion since 2024.</p>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-tiktok"></i></a>
                </div>
            </div>
            <div class="footer-section">
                <h4>Shop</h4>
                <a href="category.php?cat=men">Men</a>
                <a href="category.php?cat=women">Women</a>
                <a href="category.php?cat=kids">Kids</a>
                <a href="#">New Arrivals</a>
            </div>
            <div class="footer-section">
                <h4>Support</h4>
                <a href="#">Contact Us</a>
                <a href="#">Shipping Info</a>
                <a href="#">Returns</a>
                <a href="#">Size Guide</a>
            </div>
            <div class="footer-section">
                <h4>Newsletter</h4>
                <p>Get 10% off your first order</p>
                <form class="newsletter-form" id="newsletterForm">
                    <input type="email" placeholder="Your email">
                    <button type="submit"><i class="fas fa-paper-plane"></i></button>
                </form>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 Online Theft Clothing. All rights reserved.</p>
        </div>
    </footer>

    <div id="toast" class="toast"></div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
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

            // Existing functions (toast, cart count, etc.) must be present in script.js,
            // but we add fallback definitions here to ensure everything works.
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