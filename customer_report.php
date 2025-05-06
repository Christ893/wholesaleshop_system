<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Date filter logic
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$filterClause = '';
$params = [];

if ($startDate && $endDate) {
    $filterClause = "AND s.sale_date BETWEEN ? AND ?";
    $params = [$startDate . " 00:00:00", $endDate . " 23:59:59"];
}

$query = "
    SELECT 
        c.id,
        c.name AS customer_name,
        SUM(s.quantity) AS total_quantity,
        SUM(s.quantity * s.sale_price) AS total_amount
    FROM customers c
    JOIN sales s ON c.id = s.customer_id
    WHERE 1=1 $filterClause
    GROUP BY c.id, c.name
    ORDER BY total_amount DESC
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$report = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html>
<head>
    <title>Customer Report</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f9f9f9;
        }

        .container {
            width: 100%;
            max-width: 800px;
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        input[type="date"], button {
            padding: 8px;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ccc;
        }

        th {
            background-color: #2c3e50;
            color: white;
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
    <h2>🧍 Customer Sales Report</h2>
    <a href="dashboard.php">← Back to Dashboard</a> </br>
    <a href="reports.php">← Back to Report</a>

    <form method="GET">
        <label>From: <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" required></label>
        <label>To: <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" required></label>
        <button type="submit">Filter</button>
    </form>

    <?php if (count($report)): ?>
        <table>
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Total Quantity</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($report as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['customer_name']) ?></td>
                        <td><?= $row['total_quantity'] ?></td>
                        <td><?= number_format($row['total_amount'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No sales data found for this period.</p>
    <?php endif; ?>
</div>
</body>
</html>
