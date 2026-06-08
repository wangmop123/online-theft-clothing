<?php
// ==================== ABOUT PAGE ====================
include 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About Us - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main>
        <section class="about-hero" style="padding: 8rem 2rem 4rem; text-align: center; background: var(--navy); color: white;">
            <div class="container">
                <h1>About Online Theft Clothing</h1>
                <p style="max-width: 700px; margin: 1rem auto;">Redefining streetwear for the modern generation</p>
            </div>
        </section>
        
        <section class="about-content">
            <div class="container">
                <div class="about-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center; padding: 4rem 0;">
                    <div>
                        <h2>Our Story</h2>
                        <p style="margin-top: 1rem; color: var(--gray);">Founded in 2024, Online Theft Clothing emerged from the streets with one mission: to create bold, unapologetic fashion for those who dare to stand out. We blend premium quality with edgy designs that make a statement.</p>
                        <p style="margin-top: 1rem; color: var(--gray);">Every piece is crafted with attention to detail, using sustainable materials and ethical manufacturing processes. We're not just selling clothes; we're building a community of rebels, creators, and trendsetters.</p>
                    </div>
                    <div>
                        <img src="assets/images/about-us.jpg" alt="About Us" style="width: 100%; border-radius: 20px;">
                    </div>
                </div>
                
                <div class="values" style="text-align: center; padding: 4rem 0; background: var(--gray-light); border-radius: 30px; margin: 2rem 0;">
                    <h2>Our Values</h2>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; margin-top: 3rem;">
                        <div><i class="fas fa-gem" style="font-size: 2.5rem; color: var(--accent);"></i><h3>Quality</h3><p>Premium materials & craftsmanship</p></div>
                        <div><i class="fas fa-leaf" style="font-size: 2.5rem; color: var(--accent);"></i><h3>Sustainability</h3><p>Eco-friendly practices</p></div>
                        <div><i class="fas fa-heart" style="font-size: 2.5rem; color: var(--accent);"></i><h3>Community</h3><p>Built by the culture, for the culture</p></div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    
    <?php include 'footer.php'; ?>
    
    <div id="toast" class="toast"></div>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="script.js"></script>
</body>
</html>