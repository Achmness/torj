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
        VALUES ('$fullname', '$email', '$password', 'cashier')";

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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #5d4037 0%, #8B6F47 100%);
        }

        /* Animated wave background */
        body::before {
            content: '';
            position: absolute;
            width: 150%;
            height: 150%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120"><path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" fill="rgba(255,255,255,0.05)"/></svg>') repeat-x;
            animation: wave 20s linear infinite;
            opacity: 0.3;
            z-index: 0;
        }

        @keyframes wave {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }

        /* Form container */
        .register-box {
            position: relative;
            z-index: 1;
            background-color: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 20px;
            width: 400px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }

        .logo {
            width: 80px;
            margin-bottom: 10px;
        }

        .title {
            font-size: 28px;
            font-weight: bold;
            color: #5d4037;
            margin-bottom: 10px;
        }

        .subtitle {
            font-size: 14px;
            color: #8B6F47;
            margin-bottom: 30px;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 2px solid #E8E0D5;
            border-radius: 10px;
            outline: none;
            font-size: 14px;
            transition: border 0.3s;
        }

        input:focus {
            border-color: #ECB212;
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #5d4037 0%, #8B6F47 100%);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(93, 64, 55, 0.3);
        }

        a {
            color: #5d4037;
            font-weight: bold;
            text-decoration: none;
            transition: color 0.3s;
        }

        a:hover {
            color: #ECB212;
        }

        .error {
            background: #fee;
            color: #c33;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        /* Back button */
        .btn-back {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 24px;
            background-color: #ECB212;
            color: #3d2d00;
            text-decoration: none;
            font-weight: bold;
            border-radius: 50px;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            background-color: #d4a010;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(236, 178, 18, 0.3);
            color: #3d2d00;
        }

        p {
            margin-top: 20px;
            color: #666;
        }
    </style>
</head>
<body>

<div class="register-box">

    <img src="logo.png" class="logo">
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
