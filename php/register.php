<?php
include "db.php";

if (isset($_POST['register'])) {

    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $check);

    if (mysqli_num_rows($result) > 0) {
        $error = "Email already exists!";
    } else {
        $sql = "INSERT INTO users (fullname, email, password, role)
        VALUES ('$fullname', '$email', '$password', 'customer')";

        if (mysqli_query($conn, $sql)) {
            header("Location: login.php");
            exit();
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - The Debug Café</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../style/register.css">
</head>
<body>

<div class="register-box">

    <img src="../logo.png" class="logo">
    <div class="title">The Debug Café</div>
    <div class="subtitle">Coffee & Code</div>

    <?php if(isset($error)) { ?>
        <div class="error"><?php echo $error; ?></div>
    <?php } ?>

    <a href="index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Home</a>

    <form method="POST">
        <input type="text" name="fullname" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="register">Register</button>
    </form>

    <p>Already have account? <a href="login.php">Login</a></p>

</div>

</body>
</html>
