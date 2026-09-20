<?php
include '../config/db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = htmlspecialchars($_POST['fullname']);
    $username = htmlspecialchars($_POST['username']);
    $email = htmlspecialchars($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if username or email already exists
    $checkStmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $checkStmt->execute([$username, $email]);

    if ($checkStmt->rowCount() > 0) {
        $message = "⚠️ Username or Email already taken.";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (fullname, username, email, password) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$fullname, $username, $email, $password])) {
            $message = "✅ Registration successful. You can now log in.";
        } else {
            $message = "❌ Registration failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - UniLifeKE</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f1f1f1;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .register-box {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            width: 400px;
        }

        .register-box h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }

        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #0078D7;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            cursor: pointer;
        }

        button:hover {
            background-color: #005fa3;
        }

        .message {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
            color: red;
        }

        .message.success {
            color: green;
        }

        .login-link {
            text-align: center;
            margin-top: 15px;
        }

        .login-link a {
            color: #0078D7;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="register-box">
    <h2>Create an Account</h2>
    
    <?php if ($message): ?>
        <div class="message <?= str_contains($message, '✅') ? 'success' : '' ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="fullname" placeholder="Full Name" required>
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Register</button>
    </form>

    <div class="login-link">
        Already have an account? <a href="login.php">Login</a>
    </div>
</div>

</body>
</html>
