<?php
session_start();

// Redirect if already logged in
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
    } else {
        header("Location: customer_order.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Debug Café - Coffee & Code</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            color: #3d2d00;
            overflow-x: hidden;
        }

        /* Navigation */
        nav {
            position: fixed;
            top: 0;
            width: 100%;
            background: rgba(93, 64, 55, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: #E8E0D5;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: #E8E0D5;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: #ECB212;
        }

        .btn {
            padding: 0.6rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: 2px solid transparent;
        }

        .btn-login {
            color: #E8E0D5;
            border-color: #E8E0D5;
        }

        .btn-login:hover {
            background: #E8E0D5;
            color: #5d4037;
        }

        .btn-register {
            background: #ECB212;
            color: #3d2d00;
        }

        .btn-register:hover {
            background: #d4a010;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(236, 178, 18, 0.3);
        }

        /* Hero Section */
        .hero {
            margin-top: 70px;
            height: 90vh;
            background: linear-gradient(135deg, #5d4037 0%, #8B6F47 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #E8E0D5;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            width: 150%;
            height: 150%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120"><path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" fill="rgba(255,255,255,0.05)"/></svg>') repeat-x;
            animation: wave 20s linear infinite;
            opacity: 0.3;
        }

        @keyframes wave {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }

        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 800px;
            padding: 2rem;
        }

        .hero h1 {
            font-size: 4rem;
            margin-bottom: 1rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .hero p {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            opacity: 0.95;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .hero .btn {
            font-size: 1.1rem;
            padding: 1rem 2.5rem;
        }

        /* Best Sellers Section */
        .section {
            padding: 5rem 5%;
            max-width: 1400px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 3rem;
            color: #5d4037;
            font-weight: 700;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .product-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
        }

        .product-image {
            width: 100%;
            height: 280px;
            object-fit: cover;
        }

        .product-info {
            padding: 1.5rem;
        }

        .product-name {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #5d4037;
        }

        .product-price {
            font-size: 1.1rem;
            color: #8B6F47;
            font-weight: 700;
        }

        .badge {
            display: inline-block;
            background: #ECB212;
            color: #3d2d00;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        /* Features Section */
        .features {
            background: #f7f7f7;
            padding: 5rem 5%;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 3rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            text-align: center;
            padding: 2rem;
        }

        .feature-icon {
            font-size: 3rem;
            color: #8B6F47;
            margin-bottom: 1rem;
        }

        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #5d4037;
        }

        .feature-card p {
            color: #666;
            line-height: 1.6;
        }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, #5d4037 0%, #8B6F47 100%);
            color: #E8E0D5;
            text-align: center;
            padding: 5rem 5%;
        }

        .cta-section h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .cta-section p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .cta-section .btn {
            background: #ECB212;
            color: #3d2d00;
            font-size: 1.1rem;
            padding: 1rem 2.5rem;
        }

        .cta-section .btn:hover {
            background: #d4a010;
            transform: translateY(-2px);
        }

        /* Footer */
        footer {
            background: #5d4037;
            color: #E8E0D5;
            text-align: center;
            padding: 2rem 5%;
        }

        footer p {
            opacity: 0.8;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }

            .hero p {
                font-size: 1.2rem;
            }

            .nav-links {
                gap: 1rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .products-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav>
        <div class="logo">
            <i class="fas fa-mug-hot"></i> The Debug Café
        </div>
        <div class="nav-links">
            <a href="customer_order.php">Order Now</a>
            <a href="login.php" class="btn btn-login">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
            <a href="register.php" class="btn btn-register">
                <i class="fas fa-user-plus"></i> Register
            </a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Coffee & Code</h1>
            <p>Where ideas brew and innovation flows</p>
            <div class="hero-buttons">
                <a href="customer_order.php" class="btn btn-register">Order Now</a>
                <a href="register.php" class="btn btn-register">Get Started</a>
                <a href="login.php" class="btn btn-login">Sign In</a>
            </div>
        </div>
    </section>

    <!-- Best Sellers Section -->
    <section class="section">
        <h2 class="section-title">Our Best Sellers</h2>
        <div class="products-grid">
            <div class="product-card">
                <img src="cold2.jpg" alt="Iced Caramel Latte" class="product-image">
                <div class="product-info">
                    <span class="badge">Best Seller</span>
                    <h3 class="product-name">Iced Caramel Latte</h3>
                    <p class="product-price">₱110.00</p>
                </div>
            </div>
            <div class="product-card">
                <img src="cold4.jpg" alt="Caramel Frappe" class="product-image">
                <div class="product-info">
                    <span class="badge">Popular</span>
                    <h3 class="product-name">Caramel Frappe</h3>
                    <p class="product-price">₱135.00</p>
                </div>
            </div>
            <div class="product-card">
                <img src="bread-3.jpg" alt="Croissant" class="product-image">
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
                <img src="cold6.jpg" alt="Matcha Frappe" class="product-image">
                <div class="product-info">
                    <h3 class="product-name">Matcha Frappe</h3>
                    <p class="product-price">₱125.00</p>
                </div>
            </div>
            <div class="product-card">
                <img src="cold3.jpg" alt="Iced Mocha" class="product-image">
                <div class="product-info">
                    <h3 class="product-name">Iced Mocha</h3>
                    <p class="product-price">₱120.00</p>
                </div>
            </div>
            <div class="product-card">
                <img src="bread-8.jpg" alt="Melonpan" class="product-image">
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
    <section class="cta-section">
        <h2>Ready to Start Your Journey?</h2>
        <p>Join thousands of developers who fuel their creativity at The Debug Café</p>
        <a href="register.php" class="btn">Create Your Account</a>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; <?php echo date("Y"); ?> The Debug Café. All rights reserved.</p>
        <p style="margin-top: 0.5rem; opacity: 0.6;">Coffee & Code - Where ideas brew</p>
    </footer>
</body>
</html>
