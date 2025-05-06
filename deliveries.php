<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Handle delivery update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sale_id'])) {
    $saleId = $_POST['sale_id'];
    $isDelivered = isset($_POST['delivered']) ? 1 : 0;

    $check = $pdo->prepare("SELECT id FROM deliveries WHERE sale_id = ?");
    $check->execute([$saleId]);
    $existing = $check->fetchColumn();

    if ($existing) {
        $stmt = $pdo->prepare("UPDATE deliveries SET delivered = ?, delivery_date = NOW(), updated_by = ? WHERE sale_id = ?");
        $stmt->execute([$isDelivered, $_SESSION['user_id'], $saleId]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO deliveries (sale_id, delivered, delivery_date, updated_by) VALUES (?, ?, NOW(), ?)");
        $stmt->execute([$saleId, $isDelivered, $_SESSION['user_id']]);
    }
}

// Fetch sales with delivery info
$query = "
    SELECT s.id AS sale_id, c.name AS customer_name, p.name AS product_name, s.quantity, s.sale_date,
           d.delivered, d.delivery_date
    FROM sales s
    JOIN customers c ON s.customer_id = c.id
    JOIN products p ON s.product_id = p.id
    LEFT JOIN deliveries d ON s.id = d.sale_id
    ORDER BY s.sale_date DESC
";
$stmt = $pdo->query($query);
$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delivery Status</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f7f9fb;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1100px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #004466;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: center;
        }

        th {
            background: #f0f0f0;
        }

        button, .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            background-color: #004466;
            color: white;
            cursor: pointer;
            margin-top: 4px;
        }

        button:hover, .btn:hover {
            background-color: #006699;
        }

        .actions {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        a {
            margin-bottom: 10px;
            display: inline-block;
            color: #004466;
            text-decoration: none;
        }

        @media print {
            .actions, button, .btn {
                display: none;
            }

            body {
                background: white;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h2>🚚 Delivery Status</h2>
    <a href="dashboard.php">← Back to Dashboard</a>
    <div class="actions">
        <button onclick="window.print()">🖨️ Print</button>
        <a href="export_deliveries_excel.php" class="btn">📥 Export to Excel</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Customer</th>
                <th>Product</th>
                <th>Qty</th>
                <th>Sale Date</th>
                <th>Delivered?</th>
                <th>Delivery Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($sales as $sale): ?>
            <tr>
                <td><?= htmlspecialchars($sale['customer_name']) ?></td>
                <td><?= htmlspecialchars($sale['product_name']) ?></td>
                <td><?= $sale['quantity'] ?></td>
                <td><?= date('Y-m-d', strtotime($sale['sale_date'])) ?></td>
                <td><?= $sale['delivered'] ? '✅ Yes' : '❌ No' ?></td>
                <td><?= $sale['delivery_date'] ?? '-' ?></td>
                <td>
                    <form method="POST" action="">
                        <input type="hidden" name="sale_id" value="<?= $sale['sale_id'] ?>">
                        <label>
                            <input type="checkbox" name="delivered" <?= $sale['delivered'] ? 'checked' : '' ?>> Delivered
                        </label>
                        <br>
                        <button type="submit">Update</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
