<?php
// ==================== HEADER include ====================
// header.php - Reusable header component
?>
<header>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <a href="index.php">OTC<span class="logo-accent">THEFT</span></a>
            </div>
            <div class="nav-links">
                <a href="index.php">Home</a>
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
                    <!-- ✅ Direct link to login.php (no modal) -->
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