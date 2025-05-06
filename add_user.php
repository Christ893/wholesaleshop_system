<?php
session_start();
require '../database/db.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Redirect to dashboard if not admin
if ($_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Access Denied. Admins only.";
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (!$username || !$password || !$role) {
        $error = 'All fields are required.';
    } else {
        // Check if username exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = 'Username already exists.';
        } else {
            // Insert new user
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $hashedPassword, $role])) {
                $success = 'User created successfully.';
            } else {
                $error = 'Failed to create user.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add User (Admin Only)</title>
    <style>
        body {
            font-family: Arial;
            background: #f0f2f5;
            padding: 40px;
        }
        .form-box {
            max-width: 400px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        input, select, button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .msg {
            text-align: center;
            margin-bottom: 10px;
            color: red;
        }
        .msg.success {
            color: green;
        }
    </style>
</head>
<body>
<div class="form-box">
    <h2>👤 Add New User</h2>
    <?php if ($error): ?>
        <div class="msg"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="msg success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <select name="role" required>
            <option value="">-- Select Role --</option>
            <option value="admin">Admin</option>
            <option value="user">User</option>
        </select>
        <button type="submit">➕ Add User</button>
    </form>
    <div style="text-align:center;">
        <a href="dashboard.php">← Back to Dashboard</a>
    </div>
</div>
</body>
</html>
