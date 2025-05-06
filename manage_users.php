<?php
require 'admin_auth.php'; // This includes the admin-only session check

$stmt = $pdo->query("SELECT id, username, is_admin FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f7f7f7; }
        .container { max-width: 800px; background: #fff; padding: 20px; margin: auto; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background-color: #2c3e50; color: #fff; }
        a.button, button { padding: 6px 12px; border: none; background: #2980b9; color: white; cursor: pointer; border-radius: 4px; }
        a.button:hover, button:hover { background: #21618c; }
        form { display: inline; }
    </style>
</head>
<body>
<div class="container">
    <h2>👥 Manage Users</h2>
    <a class="button" href="add_user.php">➕ Add New User</a>
    <table>
        <thead>
            <tr><th>ID</th><th>Username</th><th>Role</th><th>Action</th></tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= $user['is_admin'] ? 'Admin' : 'User' ?></td>
                <td>
                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                    <form method="POST" action="delete_user.php" onsubmit="return confirm('Delete this user?');">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <button type="submit">🗑️ Delete</button>
                    </form>
                    <?php else: ?>
                        (You)
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
