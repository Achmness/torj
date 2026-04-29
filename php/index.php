<?php
session_start();

// Redirect if already logged in (only for staff roles)
if(isset($_SESSION['role'])) {
    if($_SESSION['role'] == 'admin') {
        header("Location: admin.php");
        exit();
    } elseif($_SESSION['role'] == 'cashier') {
        header("Location: cashier.php");
        exit();
    } elseif($_SESSION['role'] == 'barista') {
        header("Location: barista.php");
        exit();
    }
    // Customers can stay on index.php
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Debug Café - Coffee & Code</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../style/index.css">
</head>
<body>
    <!-- Navigation -->
    <nav>
        <div class="logo">
            <i class="fas fa-mug-hot"></i> The Debug Café
        </div>
        <div class="nav-links">     
            <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'customer'): ?>
                <span style="color: #E8E0D5; margin: 0 1rem;">Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?>!</span>
                <a href="logout.php" class="btn btn-login">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            <?php else: ?>
                <a href="login.php" class="btn btn-login">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
                <a href="register.php" class="btn btn-register">
                    <i class="fas fa-user-plus"></i> Register
                </a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Coffee & Code</h1>
            <p>Where ideas brew and innovation flows</p>
            <div class="hero-buttons">
                <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'customer'): ?>
                    <a href="customer_order.php" class="btn btn-register">Order Now</a>
                <?php else: ?>
                    <a href="customer_order.php" class="btn btn-register">Order Now</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Best Sellers Section -->
    <section class="section">
        <h2 class="section-title">Our Best Sellers</h2>
        <div class="products-grid">
            <div class="product-card">
                <img src="../images/cold2.jpg" alt="Iced Caramel Latte" class="product-image">
                <div class="product-info">
                    <span class="badge">Best Seller</span>
                    <h3 class="product-name">Iced Caramel Latte</h3>
                    <p class="product-price">₱110.00</p>
                </div>
            </div>
            <div class="product-card">
                <img src="../images/cold6.jpg" alt="Caramel Frappe" class="product-image">
                <div class="product-info">
                    <span class="badge">Popular</span>
                    <h3 class="product-name">Caramel Frappe</h3>
                    <p class="product-price">₱135.00</p>
                </div>
            </div>
            <div class="product-card">
                <img src="../images/bread-3.jpg" alt="Croissant" class="product-image">
                <div class="product-info">
                    <span class="badge">Fan Favorite</span>
                    <h3 class="product-name">Croissant</h3>
                    <p class="product-price">₱70.00</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="section" style="background: #f7f7f7;">
        <h2 class="section-title">Featured Products</h2>
        <div class="products-grid">
            <div class="product-card">
                <img src="../images/cold4.webp" alt="Matcha Frappe" class="product-image">
                <div class="product-info">
                    <h3 class="product-name">Matcha Frappe</h3>
                    <p class="product-price">₱125.00</p>
                </div>
            </div>
            <div class="product-card">
                <img src="../images/cold3.jpg" alt="Iced Mocha" class="product-image">
                <div class="product-info">
                    <h3 class="product-name">Iced Mocha</h3>
                    <p class="product-price">₱120.00</p>
                </div>
            </div>
            <div class="product-card">
                <img src="../images/bread-8.jpg" alt="Melonpan" class="product-image">
                <div class="product-info">
                    <h3 class="product-name">Melonpan</h3>
                    <p class="product-price">₱60.00</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <h2 class="section-title">Why Choose Us</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-coffee"></i>
                </div>
                <h3>Premium Quality</h3>
                <p>Freshly brewed coffee made from the finest beans, crafted by expert baristas who care about every cup.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-wifi"></i>
                </div>
                <h3>Perfect Workspace</h3>
                <p>High-speed Wi-Fi, comfortable seating, and a productive atmosphere for coding, studying, or meetings.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <h3>Community Vibes</h3>
                <p>Join a community of developers, creatives, and thinkers. Network, collaborate, and grow together.</p>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <?php if(!isset($_SESSION['role']) || $_SESSION['role'] != 'customer'): ?>
    <section class="cta-section">
        <h2>Ready to Start Your Journey?</h2>
        <p>Join thousands of developers who fuel their creativity at The Debug Café</p>
        <a href="register.php" class="btn">Create Your Account</a>
    </section>
    <?php endif; ?>

    <!-- Footer -->
    <footer>
        <p>&copy; <?php echo date("Y"); ?> The Debug Café. All rights reserved.</p>
        <p style="margin-top: 0.5rem; opacity: 0.6;">Coffee & Code - Where ideas brew</p>
    </footer>
</body>
</html>
