<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Fetch supplier report data
$query = "
    SELECT 
        s.name AS supplier_name,
        SUM(ps.quantity) AS total_quantity,
        SUM(ps.quantity * ps.cost_price) AS total_cost,
        AVG(ps.cost_price) AS avg_cost_price
    FROM inventory ps
    JOIN suppliers s ON ps.supplier_id = s.id
    GROUP BY s.id, s.name
    ORDER BY total_cost DESC
";

$stmt = $pdo->query($query);
$report = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Supplier Report</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f2f5f9;
            margin: 0;
            padding: 30px;
            display: flex;
            justify-content: center;
        }

        .container {
            max-width: 900px;
            width: 100%;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #003344;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #e6f3f8;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
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
    <h2>🚚 Supplier Report</h2>
    <a href="dashboard.php">← Back to Dashboard</a> </br>
    <a href="reports.php">← Back to Report</a>

    <?php if (count($report)): ?>
        <table>
            <thead>
                <tr>
                    <th>Supplier</th>
                    <th>Total Quantity Supplied</th>
                    <th>Total Cost</th>
                    <th>Average Cost per Unit</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($report as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['supplier_name']) ?></td>
                        <td><?= $row['total_quantity'] ?></td>
                        <td><?= number_format($row['total_cost'], 2) ?></td>
                        <td><?= number_format($row['avg_cost_price'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No supplier data available.</p>
    <?php endif; ?>
</div>
</body>
</html>
