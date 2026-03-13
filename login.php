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

            if($row['role'] == "admin"){
                header("Location: admin.php"); 
            }
            elseif($row['role'] == "cashier") {
                header("Location: cashier.php"); 
            }
            else {
                header("Location: barista.php"); 
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
    <style>
         body {
            margin: 0;
            font-family: Arial, sans-serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        /* Blurred logo background */
        body::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 100%;
            height: 100%;
            background: url('logo.png') center center no-repeat;
            background-size: cover;
            filter: blur(20px);
            -webkit-filter: blur(20px); /* Safari */
            z-index: 0;
            opacity: 0.6; /* adjust visibility */
        }

        /* Form container */
        .login-box {
            position: relative;
            z-index: 1; /* above blurred background */
            background-color: rgba(244, 225, 193, 0.95); /* semi-transparent */
            padding: 40px;
            border: 4px solid #f2b705;
            border-radius: 15px;
            width: 370px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }

        .logo {
            width: 80px;
            margin-bottom: 10px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            color: #4b3200;
            margin-bottom: 20px;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 2px solid #f2b705;
            border-radius: 8px;
            outline: none;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #4b3200;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        button:hover {
            background-color: #6b4500;
            transform: translateY(-2px);
        }

        a {
            color: #4b3200;
            font-weight: bold;
            text-decoration: none;
        }

        .error {
            color: red;
            margin-bottom: 10px;
        }

        /* Back button */
        .btn-back {
            display: inline-block;
            margin-bottom: 20px;
            padding: 8px 20px;
            background-color: #f2b705;
            color: #4b3200;
            text-decoration: none;
            font-weight: bold;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            background-color: #e0a700;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<div class="login-box">

    <img src="logo.png" class="logo">
    <div class="title">The Debug Café</div>

    <?php if(isset($error)) { ?>
        <div class="error"><?php echo $error; ?></div>
    <?php } ?>

    <a href="index.php" class="btn-back">← Back</a>

    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login">Login</button>
    </form>

    <br>
    <p>No account? <a href="register.php">Register</a></p>

</div>

<style>
    .btn-back {
    display: inline-block;
    margin-bottom: 20px;
    padding: 8px 20px;
    background-color: #f2b705;
    color: #4b3200;
    text-decoration: none;
    font-weight: bold;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-back:hover {
    background-color: #e0a700;
    transform: translateY(-2px);
}
</style>

</body>
</html>
