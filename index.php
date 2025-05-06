<?php
session_start();
// You can show messages like registration success or errors
$registered = isset($_GET['registered']);
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Wholesale Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #f1f3f6;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .login-container h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #333;
        }

        .login-container form {
            display: flex;
            flex-direction: column;
        }

        .login-container input[type="text"],
        .login-container input[type="password"] {
            padding: 10px;
            margin-bottom: 1rem;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1rem;
        }

        .login-container button {
            padding: 10px;
            background: #0056b3;
            color: white;
            font-size: 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .login-container button:hover {
            background: #004494;
        }

        .login-container .link {
            margin-top: 1rem;
            text-align: center;
        }

        .login-container .link a {
            color: #0056b3;
            text-decoration: none;
        }

        .message {
            color: green;
            text-align: center;
            margin-bottom: 1rem;
        }

        .error {
            color: red;
            text-align: center;
            margin-bottom: 1rem;
        }
        a {
            margin-bottom: 10px;
            display: inline-block;
            color: #004466;
            text-decoration: none;
        }

        @media (max-width: 500px) {
            .login-container {
                margin: 10px;
                padding: 1.5rem;
            }
            a {
            margin-bottom: 10px;
            display: inline-block;
            color: #004466;
            text-decoration: none;
        }
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Login</h2>

    <?php if ($registered): ?>
        <div class="message">Admin registered successfully. Please log in.</div>
    <?php endif; ?>

    <?php if ($error === 'admin_exists'): ?>
        <div class="error">Admin account already exists.</div>
    <?php endif; ?>

    <form method="POST" action="../actions/login.php">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>

    <div class="link">
        <a href="register_admin.php">➕ Create Admin Account</a>
    </div>
</div>

</body>
</html>
