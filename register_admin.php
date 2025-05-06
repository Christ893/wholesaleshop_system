<?php
require '../database/db.php';

// Check if an admin already exists
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
$adminCount = $stmt->fetchColumn();

if ($adminCount > 0) {
    // Redirect or block access
    die("Admin account already exists. Contact system administrator.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')");
    $stmt->execute([$username, $password]);

    header("Location: index.php?registered=1");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Admin Account</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="container">
    <h2>Create Admin Account</h2>
    <form method="POST">
        <label>Username:</label><br>
        <input type="text" name="username" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <label>Secret Code:</label><br>
        <input type="text" name="secret" required><br><br>

        <button type="submit">Create Admin</button>
        
    </form>
    <p><a href="index.php">← Back to Login</a></p>
</div>
</body>
</html>
