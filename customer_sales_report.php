<?php
session_start();
require '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Fetch customers
$customers = $pdo->query("SELECT id, name FROM customers ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

$selectedCustomer = $_GET['customer_id'] ?? '';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$sales = [];
$total = 0;

if ($selectedCustomer && $startDate && $endDate) {
    $stmt = $pdo->prepare("
        SELECT s.sale_date, p.name AS product_name, s.quantity, s.sale_price,
               (s.quantity * s.sale_price) AS total_price
        FROM sales s
        JOIN products p ON s.product_id = p.id
        WHERE s.customer_id = ? AND s.sale_date BETWEEN ? AND ?
        ORDER BY s.sale_date DESC
    ");
    $stmt->execute([$selectedCustomer, $startDate, $endDate]);
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Total amount spent
    $totalStmt = $pdo->prepare("
        SELECT SUM(s.quantity * s.sale_price) AS total
        FROM sales s
        WHERE s.customer_id = ? AND s.sale_date BETWEEN ? AND ?
    ");
    $totalStmt->execute([$selectedCustomer, $startDate, $endDate]);
    $total = $totalStmt->fetch()['total'] ?? 0;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customer Sales Report</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 8px; border: 1px solid #ccc; text-align: left; }
        .form-group { margin-bottom: 15px; }
        .summary { margin-top: 20px; font-weight: bold; background: #f0f0f0; padding: 10px; }

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
    <h2>👤 Customer Sales Report</h2>

    <form method="GET">
        <div class="form-group">
            <label>Select Customer:</label>
            <select name="customer_id" required>
                <option value="">-- Choose --</option>
                <?php foreach ($customers as $cust): ?>
                    <option value="<?= $cust['id'] ?>" <?= ($cust['id'] == $selectedCustomer) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cust['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>From:</label>
            <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" required>
            <label>To:</label>
            <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" required>
            <button type="submit">View Report</button>
        </div>
    </form>
    <a href="dashboard.php">← Back to Dashboard</a>
    <?php if ($sales): ?>
    <form method="POST" action="export_customer_sales.php" style="margin-top: 10px;">
        <input type="hidden" name="customer_id" value="<?= htmlspecialchars($selectedCustomer) ?>">
        <input type="hidden" name="start_date" value="<?= htmlspecialchars($startDate) ?>">
        <input type="hidden" name="end_date" value="<?= htmlspecialchars($endDate) ?>">
        <button type="submit" name="export_type" value="csv">Export CSV</button>
        <button type="submit" name="export_type" value="pdf">Export PDF</button>
    </form>
<?php endif; ?>


    <?php if ($sales): ?>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sales as $sale): ?>
                    <tr>
                        <td><?= htmlspecialchars($sale['sale_date']) ?></td>
                        <td><?= htmlspecialchars($sale['product_name']) ?></td>
                        <td><?= $sale['quantity'] ?></td>
                        <td><?= number_format($sale['sale_price'], 2) ?></td>
                        <td><?= number_format($sale['total_price'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="summary">
            Total Spent by Customer: ₦<?= number_format($total, 2) ?>
        </div>
    <?php elseif ($selectedCustomer): ?>
        <p>No sales found for this customer in the selected period.</p>
    <?php endif; ?>
</div>
</body>
</html>
