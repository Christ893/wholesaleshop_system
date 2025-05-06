<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$searchTerm = $_GET['search'] ?? '';

if ($searchTerm) {
    $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE name LIKE ?");
    $stmt->execute(["%$searchTerm%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM suppliers ORDER BY name");
}

$suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Suppliers</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .supplier-table {
            width: 100%;
            border-collapse: collapse;
        }
        .supplier-table th, .supplier-table td {
            border: 1px solid #ccc;
            padding: 10px;
        }
        .supplier-table th {
            background-color: #f0f0f0;
        }
        a {
            margin-bottom: 10px;
            display: inline-block;
            color: #004466;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>🏭 Registered Suppliers</h2>

    <p><a href="register_supplier.php">➕ Add New Supplier</a></p>

    <form method="GET" class="search-bar">
        <input type="text" name="search" placeholder="Search by name..." value="<?= htmlspecialchars($searchTerm) ?>">
        <button type="submit">Search</button>
    </form>

    <?php if (count($suppliers) > 0): ?>
        <a href="dashboard.php">← Back to Dashboard</a>
        <table class="supplier-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Address</th>
                    <th>Registered At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($suppliers as $supplier): ?>
                    <tr>
                        <td><?= htmlspecialchars($supplier['name']) ?></td>
                        <td><?= htmlspecialchars($supplier['contact']) ?></td>
                        <td><?= htmlspecialchars($supplier['address']) ?></td>
                        <td><?= htmlspecialchars($supplier['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No suppliers found.</p>
    <?php endif; ?>
</div>
</body>
</html>
