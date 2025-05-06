<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$searchTerm = $_GET['search'] ?? '';

// Search customers if search term is provided
if ($searchTerm) {
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE name LIKE ?");
    $stmt->execute(["%$searchTerm%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM customers ORDER BY name");
}

$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customers</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .search-bar {
            margin-bottom: 20px;
        }

        .customer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .customer-table th, .customer-table td {
            border: 1px solid #ccc;
            padding: 10px;
        }

        .customer-table th {
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
    <h2>👥 Registered Customers</h2>

    <!-- Search Bar -->
    <form method="GET" class="search-bar">
        <input type="text" name="search" placeholder="Search by name..." value="<?= htmlspecialchars($searchTerm) ?>">
        <button type="submit">Search</button>
        <a href="dashboard.php">← Back to Dashboard</a>
    </form>

    <?php if (count($customers) > 0): ?>
        <table class="customer-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Address</th>
                    <th>Registered At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td><?= htmlspecialchars($customer['name']) ?></td>
                        <td><?= htmlspecialchars($customer['contact']) ?></td>
                        <td><?= htmlspecialchars($customer['address']) ?></td>
                        <td><?= htmlspecialchars($customer['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No customers found.</p>
    <?php endif; ?>
</div>
</body>
</html>
