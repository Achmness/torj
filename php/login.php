<?php
session_start();
include "db.php";

if (isset($_POST['login'])) {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {

        $row = mysqli_fetch_assoc($result);

        if (password_verify($password, $row['password'])) {

            $_SESSION['user_id'] = $row['id'];
            $_SESSION['fullname'] = $row['fullname'];
            $_SESSION['role'] = $row['role']; 

            switch($row['role']) {
                case "admin":
                    header("Location: admin.php");
                    break;
                case "cashier":
                    header("Location: cashier.php");
                    break;
                default:
                    header("Location: barista.php");
                    break;
            }
            exit();
        } else {
            $error = "Wrong password!";
        }

    } else {
        $error = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - The Debug Café</title>
    <link rel="stylesheet" href="../style/login.css">
</head>
<body>

<div class="login-box">

    <img src="../logo.png" class="logo">
    <div class="title">The Debug Café</div>
    <div class="subtitle">Coffee & Code</div>

    <?php if(isset($error)) { ?>
        <div class="error"><?php echo $error; ?></div>
    <?php } ?>

    <a href="index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Home</a>

    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login">Login</button>
    </form>

    <p>No account? <a href="register.php">Register</a></p>

</div>

</body>
</html>
