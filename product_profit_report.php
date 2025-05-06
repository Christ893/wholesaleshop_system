<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Date filters
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
        p.id,
        p.name,
        SUM(s.quantity) AS total_sold,
        AVG(s.sale_price) AS avg_sale_price,
        (
            SELECT AVG(ps.cost_price)
            FROM product_supply ps
            WHERE ps.product_id = p.id
        ) AS avg_cost_price,
        SUM(s.quantity * s.sale_price) AS total_revenue,
        SUM(s.quantity * s.sale_price) - (
            SUM(s.quantity) * (
                SELECT AVG(ps.cost_price)
                FROM product_supply ps
                WHERE ps.product_id = p.id
            )
        ) AS profit
    FROM sales s
    JOIN products p ON s.product_id = p.id
    WHERE 1=1 $filterClause
    GROUP BY p.id, p.name
    ORDER BY profit DESC
";



$stmt = $pdo->prepare($query);
$stmt->execute($params);
$report = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html>
<head>
    <title>Product Profit Report</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        .profit-positive {
            color: green;
            font-weight: bold;
        }

        .profit-negative {
            color: red;
            font-weight: bold;
        }

        form {
            margin-bottom: 20px;
        }

        input[type="date"] {
            padding: 5px;
        }

        body {
        margin: 0;
        padding: 0;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Segoe UI', sans-serif;
        background-color: #f4f6f8;
    }

    .container {
        width: 100%;
        max-width: 960px;
        background: #fff;
        padding: 30px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        border-radius: 12px;
    }

    h2 {
        text-align: center;
        margin-bottom: 25px;
        color: #004466;
    }

    form {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 15px;
        margin-bottom: 20px;
    }

    form label {
        align-self: center;
        font-weight: bold;
    }

    input[type="date"] {
        padding: 8px 10px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 14px;
    }

    button[type="submit"] {
        padding: 10px 18px;
        background-color: #004466;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    button[type="submit"]:hover {
        background-color: #006699;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
        font-size: 15px;
    }

    th, td {
        border: 1px solid #ccc;
        padding: 10px;
        text-align: left;
    }

    th {
        background-color: #e0f0f8;
        color: #003344;
    }

    .profit-positive {
        color: green;
        font-weight: bold;
    }

    .profit-negative {
        color: red;
        font-weight: bold;
    }
    a {
            margin-bottom: 10px;
            display: inline-block;
            color: #004466;
            text-decoration: none;
        }

    @media (max-width: 768px) {
        .container {
            padding: 20px;
            margin: 10px;
        }

        form {
            flex-direction: column;
            align-items: stretch;
        }

        input[type="date"], button[type="submit"] {
            width: 100%;
        }

        table, th, td {
            font-size: 14px;
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
<div class="container">
    <h2>📦 Product-Level Profit Report</h2>

    <form method="GET">
        <label>From: </label>
        <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" required>
        <label>To: </label>
        <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" required>
        <button type="submit">Filter</button> 
    </form>
    <a href="dashboard.php">← Back to Dashboard</a> </br>
    <a href="reports.php">← Back to Report</a>

    <?php if (count($report)): ?>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity Sold</th>
                    <th>Avg Sale Price</th>
                    <th>Avg Cost Price</th>
                    <th>Total Revenue</th>
                    <th>Profit/Loss</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($report as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= $row['total_sold'] ?></td>
                        <td><?= number_format($row['avg_sale_price'], 2) ?></td>
                        <td><?= number_format($row['avg_cost_price'], 2) ?></td>
                        <td><?= number_format($row['total_revenue'], 2) ?></td>
                        <td class="<?= $row['profit'] >= 0 ? 'profit-positive' : 'profit-negative' ?>">
                            <?= number_format($row['profit'], 2) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No sales data available for this period.</p>
    <?php endif; ?>
</div>
</body>
</html>
