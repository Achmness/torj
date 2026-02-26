<?php
session_start();

// Redirect if already logged in
if(isset($_SESSION['role'])) {
    if($_SESSION['role'] == 'admin') {
        header("Location: admin.php");
        exit();
    } elseif($_SESSION['role'] == 'barista') {
        header("Location: barista.php");
        exit();
    } else {
        header("Location: home.php"); // cashier or other roles
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Debug Café</title>
    <link rel="stylesheet" href="landing.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <header class="hero-section">
        <div class="hero-content">
    <h1>Welcome to <span>The Debug Café</span></h1>
    <p class="tagline">Coffee & Code – Where ideas brew!</p>
    <div class="hero-buttons">
        <a href="login.php" class="btn btn-login">
            <i class="fas fa-sign-in-alt"></i> Login
        </a>
        <a href="register.php" class="btn btn-register">
            <i class="fas fa-user-plus"></i> Register
        </a>
    </div>
</div>
    </header>

    <section class="features">
        <h2>Our Features</h2>
        <div class="feature-cards">
            <div class="feature-card">
                <i class="fas fa-coffee"></i>
                <h3>Fresh Coffee</h3>
                <p>Enjoy freshly brewed coffee while working on your code or projects.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-laptop-code"></i>
                <h3>Cozy Workspace</h3>
                <p>Comfortable space with fast Wi-Fi for coding, studying, or meetings.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-users"></i>
                <h3>Friendly Staff</h3>
                <p>Our baristas and staff are ready to make your experience seamless.</p>
            </div>
        </div>
    </section>

    <footer class="landing-footer">
        <p>&copy; <?php echo date("Y"); ?> The Debug Café. All rights reserved.</p>
    </footer>
</body>
</html>